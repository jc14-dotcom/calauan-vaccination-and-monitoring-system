<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\BackupSetting;
use ZipArchive;
use Carbon\Carbon;

class BackupController extends Controller
{
    private $backupPath = 'backups';
    
    // Development mode flag - set to false in production to enable delete restrictions
    private $devMode = true;
    
    /**
     * Show backup management page
     */
    public function index()
    {
        $backups = $this->getBackupsList();
        $settings = BackupSetting::getInstance();
        
        $dbInfo = [
            'name' => config('database.connections.mysql.database'),
            'size' => $this->getDatabaseSize(),
        ];
        
        return view('health_worker.backup', compact('backups', 'dbInfo', 'settings'));
    }
    
    /**
     * Get backup settings (API)
     */
    public function getSettings()
    {
        $settings = BackupSetting::getInstance();
        $timezone = BackupSetting::TIMEZONE; // Asia/Manila
        
        return response()->json([
            'success' => true,
            'settings' => [
                'schedule' => $settings->schedule,
                'enabled' => $settings->enabled,
                'last_backup_at' => $settings->last_backup_at 
                    ? $settings->last_backup_at->setTimezone($timezone)->format('F d, Y h:i A') . ' (PHT)' 
                    : null,
                'next_backup_at' => $settings->next_backup_at 
                    ? $settings->next_backup_at->setTimezone($timezone)->format('F d, Y h:i A') . ' (PHT)'
                    : null,
                'schedule_description' => $settings->getScheduleDescription(),
                'retention_count' => $settings->retention_count,
                'server_time' => BackupSetting::getPhilippineTime()->format('F d, Y h:i A') . ' (PHT)',
            ]
        ]);
    }
    
    /**
     * Update backup settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'schedule' => 'required|in:daily,weekly,monthly,quarterly,yearly',
        ]);
        
        $settings = BackupSetting::getInstance();
        $settings->schedule = $request->schedule;
        $settings->enabled = true; // Always enabled when settings are saved
        $settings->retention_count = BackupSetting::getRetentionCount($request->schedule);
        $settings->next_backup_at = $settings->calculateNextBackupTime();
        $settings->save();
        
        $timezone = BackupSetting::TIMEZONE;
        
        return response()->json([
            'success' => true,
            'message' => 'Backup settings updated successfully!',
            'settings' => [
                'schedule' => $settings->schedule,
                'next_backup_at' => $settings->next_backup_at->setTimezone($timezone)->format('F d, Y h:i A') . ' (PHT)',
                'schedule_description' => $settings->getScheduleDescription(),
                'retention_count' => $settings->retention_count,
            ]
        ]);
    }
    
    /**
     * Create a new backup (manual trigger)
     */
    public function create(Request $request)
    {
        try {
            $result = $this->performBackup('manual');
            
            if ($result['success']) {
                // Update last backup time (use PHT)
                $settings = BackupSetting::getInstance();
                $settings->last_backup_at = Carbon::now('Asia/Manila');
                $settings->save();
            }
            
            return response()->json($result, $result['success'] ? 200 : 500);
            
        } catch (\Exception $e) {
            Log::error('Backup creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create scheduled backup (called from command)
     */
    public function createScheduledBackup(): array
    {
        return $this->performBackup('scheduled');
    }
    
    /**
     * Perform the actual backup
     */
    private function performBackup(string $type = 'manual'): array
    {
        set_time_limit(600); // 10 minutes timeout
        
        // Use Philippine timezone for all timestamps
        $phtNow = Carbon::now('Asia/Manila');
        $timestamp = $phtNow->format('Y-m-d_His');
        $backupName = "backup_{$timestamp}";
        $tempPath = storage_path("app/temp_backup_{$timestamp}");
        
        try {
            // Create temporary directory
            if (!File::exists($tempPath)) {
                File::makeDirectory($tempPath, 0755, true);
            }
            
            // 1. Export MySQL database using pure PHP
            $sqlFile = $tempPath . '/database.sql';
            $this->exportDatabasePHP($sqlFile);
            
            // 2. Create backup info file
            $infoFile = $tempPath . '/backup-info.json';
            $this->createBackupInfo($infoFile, $type);
            
            // 3. Copy .env file (optional - skip if not accessible)
            try {
                if (File::exists(base_path('.env'))) {
                    File::copy(base_path('.env'), $tempPath . '/.env.backup');
                }
            } catch (\Exception $e) {
                // Skip .env backup if not accessible
            }
            
            // 4. Create ZIP file
            $zipFile = storage_path("app/{$this->backupPath}/{$backupName}.zip");
            
            // Ensure backup directory exists
            if (!File::exists(storage_path("app/{$this->backupPath}"))) {
                File::makeDirectory(storage_path("app/{$this->backupPath}"), 0755, true);
            }
            
            $this->createZipArchive($tempPath, $zipFile);
            
            // 5. Clean up temporary directory
            File::deleteDirectory($tempPath);
            
            // 6. Clean old backups based on retention policy
            $this->cleanOldBackups();
            
            return [
                'success' => true,
                'message' => 'Backup created successfully!',
                'backup' => [
                    'name' => $backupName . '.zip',
                    'size' => $this->formatBytes(File::size($zipFile)),
                    'date' => Carbon::now('Asia/Manila')->format('F d, Y h:i A')
                ]
            ];
            
        } catch (\Exception $e) {
            // Clean up on error
            if (File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }
            
            throw $e;
        }
    }
    
    /**
     * Download a backup file
     */
    public function download($filename)
    {
        $filePath = storage_path("app/{$this->backupPath}/{$filename}");
        
        if (!File::exists($filePath)) {
            abort(404, 'Backup file not found');
        }
        
        return response()->download($filePath);
    }
    
    /**
     * Delete a backup file
     */
    public function delete($filename)
    {
        try {
            $filePath = storage_path("app/{$this->backupPath}/{$filename}");
            
            if (!File::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }
            
            // Get all backups
            $backups = $this->getBackupsList();
            
            // Find the backup being deleted
            $backupToDelete = null;
            $isNewest = false;
            
            foreach ($backups as $index => $backup) {
                if ($backup['name'] === $filename) {
                    $backupToDelete = $backup;
                    $isNewest = ($index === 0); // First in the sorted list is newest
                    break;
                }
            }
            
            if (!$backupToDelete) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup not found in list'
                ], 404);
            }
            
            // Delete protection checks (only if not in dev mode)
            if (!$this->devMode) {
                // Cannot delete the most recent backup
                if ($isNewest && count($backups) > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete the most recent backup. At least one backup must be kept.'
                    ], 403);
                }
                
                // Cannot delete backups less than 24 hours old
                $backupTime = Carbon::createFromTimestamp($backupToDelete['timestamp']);
                if ($backupTime->diffInHours(Carbon::now()) < 24) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete backups less than 24 hours old.'
                    ], 403);
                }
            }
            
            // Delete the file
            File::delete($filePath);
            
            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Backup deletion failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Restore from backup
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:512000', // Max 500MB
        ]);
        
        try {
            set_time_limit(600); // 10 minutes timeout
            
            // Use Philippine timezone for all timestamps
            $phtNow = Carbon::now('Asia/Manila');
            
            // Create safety backup first
            $safetyBackupName = 'safety_backup_' . $phtNow->format('Y-m-d_His');
            $this->createSafetyBackup($safetyBackupName);
            
            $timestamp = $phtNow->format('Y-m-d_His');
            $tempPath = storage_path("app/temp_restore_{$timestamp}");
            
            // Create temporary directory
            File::makeDirectory($tempPath, 0755, true);
            
            // Save uploaded file
            $uploadedFile = $request->file('backup_file');
            $zipPath = $tempPath . '/backup.zip';
            $uploadedFile->move($tempPath, 'backup.zip');
            
            // Extract ZIP file
            $extractPath = $tempPath . '/extracted';
            File::makeDirectory($extractPath, 0755, true);
            
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                throw new \Exception('Failed to open backup file');
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Validate backup structure
            if (!File::exists($extractPath . '/database.sql')) {
                throw new \Exception('Invalid backup file: database.sql not found');
            }
            
            // Restore database
            // Note: User will be logged out after restore - this is expected behavior
            // similar to how OS restores require a restart
            $this->importDatabasePHP($extractPath . '/database.sql');
            
            // Clean up
            File::deleteDirectory($tempPath);
            
            // Clear all caches
            try {
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('view:clear');
            } catch (\Exception $e) {
                // Cache clear might fail but restore succeeded
            }
            
            return response()->json([
                'success' => true,
                'message' => 'System restored successfully! You will be redirected to login.',
                'safety_backup' => $safetyBackupName . '.zip',
                'requires_login' => true
            ]);
            
        } catch (\Exception $e) {
            // Clean up on error
            if (isset($tempPath) && File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }
            
            Log::error('Restore failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Restore from a server-side backup file
     */
    public function restoreFromServer(Request $request, $filename)
    {
        try {
            set_time_limit(600); // 10 minutes timeout
            
            $backupPath = storage_path('app/backups');
            $zipPath = $backupPath . '/' . $filename;
            
            // Validate file exists
            if (!File::exists($zipPath)) {
                Log::warning('Backup file not found', ['path' => $zipPath]);
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }
            
            // Validate filename format (security check)
            if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{6}\.zip$|^safety_backup_\d{4}-\d{2}-\d{2}_\d{6}\.zip$/', $filename)) {
                Log::warning('Invalid filename format', ['filename' => $filename]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid backup filename'
                ], 400);
            }
            
            // Create safety backup first
            $safetyBackupName = 'safety_backup_' . Carbon::now('Asia/Manila')->format('Y-m-d_His');
            $this->createSafetyBackup($safetyBackupName);
            
            $timestamp = Carbon::now('Asia/Manila')->format('Y-m-d_His');
            $tempPath = storage_path("app/temp_restore_{$timestamp}");
            
            // Create temporary directory
            File::makeDirectory($tempPath, 0755, true);
            
            // Extract ZIP file
            $extractPath = $tempPath . '/extracted';
            File::makeDirectory($extractPath, 0755, true);
            
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                throw new \Exception('Failed to open backup file');
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Validate backup structure
            if (!File::exists($extractPath . '/database.sql')) {
                throw new \Exception('Invalid backup file: database.sql not found');
            }
            
            // Restore database
            // Note: User will be logged out after restore - this is expected behavior
            // similar to how OS restores require a restart
            $this->importDatabasePHP($extractPath . '/database.sql');
            
            // Clean up
            File::deleteDirectory($tempPath);
            
            // Clear all caches
            try {
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('view:clear');
            } catch (\Exception $e) {
                // Cache clear might fail but restore succeeded
            }
            
            return response()->json([
                'success' => true,
                'message' => 'System restored successfully from ' . $filename . '! You will be redirected to login.',
                'safety_backup' => $safetyBackupName . '.zip',
                'requires_login' => true
            ]);
            
        } catch (\Exception $e) {
            // Clean up on error
            if (isset($tempPath) && File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }
            
            Log::error('Restore from server failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export MySQL database using pure PHP (no mysqldump dependency)
     * Works on shared hosting where exec() may be disabled
     */
    private function exportDatabasePHP($outputFile)
    {
        $sql = "-- Database Backup\n";
        $sql .= "-- Generated: " . Carbon::now('Asia/Manila')->format('F d, Y h:i:s A') . " (PHT)\n";
        $sql .= "-- PHP Version: " . PHP_VERSION . "\n";
        $sql .= "-- Laravel Version: " . app()->version() . "\n\n";
        
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";
        
        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $tableKey = "Tables_in_{$dbName}";
        
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            $sql .= "-- Table: {$tableName}\n";
            
            // Get CREATE TABLE statement
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createStatement = $createTable[0]->{'Create Table'};
            
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createStatement . ";\n\n";
            
            // Get table data
            $rows = DB::table($tableName)->get();
            
            if ($rows->count() > 0) {
                // Get column names
                $columns = array_keys((array) $rows->first());
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                // Batch inserts (500 rows at a time)
                $chunks = $rows->chunk(500);
                
                foreach ($chunks as $chunk) {
                    $sql .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES\n";
                    
                    $values = [];
                    foreach ($chunk as $row) {
                        $rowValues = [];
                        foreach ((array) $row as $value) {
                            if ($value === null) {
                                $rowValues[] = 'NULL';
                            } else {
                                $rowValues[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }
                    
                    $sql .= implode(",\n", $values) . ";\n\n";
                }
            }
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        File::put($outputFile, $sql);
        
        if (!File::exists($outputFile) || File::size($outputFile) === 0) {
            throw new \Exception('Failed to create database export file');
        }
    }
    
    /**
     * Import MySQL database using pure PHP
     * Note: This will restore the entire database including sessions table
     * User will need to re-login after restore (expected behavior)
     */
    private function importDatabasePHP($sqlFile)
    {
        $sql = File::get($sqlFile);
        
        // Execute the SQL directly - full restore
        DB::unprepared($sql);
    }
    
    /**
     * Create backup info file
     */
    private function createBackupInfo($filePath, $type = 'manual')
    {
        $info = [
            'type' => $type,
            'created_at' => Carbon::now('Asia/Manila')->toISOString(),
            'created_at_pht' => Carbon::now('Asia/Manila')->format('F d, Y h:i:s A') . ' (PHT)',
            'timezone' => 'Asia/Manila',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database' => config('database.connections.mysql.database'),
            'app_url' => config('app.url'),
        ];
        
        File::put($filePath, json_encode($info, JSON_PRETTY_PRINT));
    }
    
    /**
     * Create ZIP archive from directory
     */
    private function createZipArchive($sourcePath, $zipFile)
    {
        $zip = new ZipArchive;
        
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Failed to create ZIP archive');
        }
        
        $files = File::allFiles($sourcePath);
        
        foreach ($files as $file) {
            $relativePath = str_replace($sourcePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            // Normalize path separators for ZIP
            $relativePath = str_replace('\\', '/', $relativePath);
            $zip->addFile($file->getPathname(), $relativePath);
        }
        
        $zip->close();
    }
    
    /**
     * Create safety backup before restore
     */
    private function createSafetyBackup($backupName)
    {
        $tempPath = storage_path("app/temp_safety_backup");
        
        if (File::exists($tempPath)) {
            File::deleteDirectory($tempPath);
        }
        File::makeDirectory($tempPath, 0755, true);
        
        // Export current database
        $sqlFile = $tempPath . '/database.sql';
        $this->exportDatabasePHP($sqlFile);
        
        // Create info file
        $infoFile = $tempPath . '/backup-info.json';
        File::put($infoFile, json_encode([
            'type' => 'safety_backup',
            'created_at' => Carbon::now('Asia/Manila')->toISOString(),
            'created_at_pht' => Carbon::now('Asia/Manila')->format('F d, Y h:i:s A') . ' (PHT)',
            'note' => 'Automatic safety backup created before restore operation'
        ], JSON_PRETTY_PRINT));
        
        // Ensure backup directory exists
        if (!File::exists(storage_path("app/{$this->backupPath}"))) {
            File::makeDirectory(storage_path("app/{$this->backupPath}"), 0755, true);
        }
        
        // Create ZIP
        $zipFile = storage_path("app/{$this->backupPath}/{$backupName}.zip");
        $this->createZipArchive($tempPath, $zipFile);
        
        // Clean up
        File::deleteDirectory($tempPath);
    }
    
    /**
     * Get list of existing backups
     */
    public function getBackupsList()
    {
        $backupDir = storage_path("app/{$this->backupPath}");
        
        if (!File::exists($backupDir)) {
            return [];
        }
        
        $files = File::files($backupDir);
        $backups = [];
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'zip') {
                $timestamp = $file->getMTime();
                // Convert to Philippine timezone for display
                $backupTime = Carbon::createFromTimestamp($timestamp)->setTimezone('Asia/Manila');
                
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'size_bytes' => $file->getSize(),
                    'date' => $backupTime->format('F d, Y h:i A'),
                    'timestamp' => $timestamp,
                    'is_safety' => str_contains($file->getFilename(), 'safety_backup'),
                    'is_recent' => $backupTime->diffInHours(Carbon::now('Asia/Manila')) < 24,
                ];
            }
        }
        
        // Sort by timestamp descending (newest first)
        usort($backups, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        // Mark the newest backup
        if (count($backups) > 0) {
            $backups[0]['is_newest'] = true;
        }
        
        return $backups;
    }
    
    /**
     * Clean old backups based on retention policy
     */
    private function cleanOldBackups()
    {
        $settings = BackupSetting::getInstance();
        $backups = $this->getBackupsList();
        $retentionCount = $settings->retention_count;
        
        // Separate regular backups from safety backups
        $regularBackups = array_filter($backups, fn($b) => !$b['is_safety']);
        $safetyBackups = array_filter($backups, fn($b) => $b['is_safety']);
        
        // Keep only the specified number of regular backups
        if (count($regularBackups) > $retentionCount) {
            $toDelete = array_slice(array_values($regularBackups), $retentionCount);
            
            foreach ($toDelete as $backup) {
                $filePath = storage_path("app/{$this->backupPath}/{$backup['name']}");
                if (File::exists($filePath)) {
                    File::delete($filePath);
                    Log::info("Auto-deleted old backup: {$backup['name']}");
                }
            }
        }
        
        // Keep only the last 3 safety backups
        if (count($safetyBackups) > 3) {
            $toDelete = array_slice(array_values($safetyBackups), 3);
            
            foreach ($toDelete as $backup) {
                $filePath = storage_path("app/{$this->backupPath}/{$backup['name']}");
                if (File::exists($filePath)) {
                    File::delete($filePath);
                    Log::info("Auto-deleted old safety backup: {$backup['name']}");
                }
            }
        }
    }
    
    /**
     * Get database size
     */
    private function getDatabaseSize()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$dbName]);
            
            $size = $result[0]->size_mb ?? 0;
            return $size . ' MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
    
    /**
     * Format bytes to human readable size
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
