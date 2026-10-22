<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VaccinationReportService;
use Carbon\Carbon;

class AutoSaveQuarterlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:auto-save-quarterly 
                            {--quarter= : Specific quarter to save (1-4)}
                            {--year= : Specific year to save}
                            {--force : Force save even if report already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically save quarterly vaccination report snapshot';

    protected $reportService;

    /**
     * Create a new command instance.
     */
    public function __construct(VaccinationReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-save quarterly report...');
        
        // Get target quarter and year
        $currentQuarter = (int)ceil(Carbon::now()->month / 3);
        $targetQuarter = $this->option('quarter') ?: ($currentQuarter === 1 ? 4 : $currentQuarter - 1);
        $targetYear = $this->option('year') ?: ($currentQuarter === 1 ? Carbon::now()->year - 1 : Carbon::now()->year);
        $force = $this->option('force');
        
        $this->info("Target: {$targetYear} Q{$targetQuarter}");
        
        // Calculate month range for this quarter
        $monthStart = ($targetQuarter - 1) * 3 + 1;
        $monthEnd = $targetQuarter * 3;
        
        // Find the next version number for this period
        // System supports multiple versions (v1, v2, v3) for comparison
        // IMPORTANT: Must filter by ALL date fields to avoid conflicts with NULL or different date ranges
        $maxVersion = \App\Models\VaccinationReportSnapshot::where('year', $targetYear)
            ->where('quarter_start', $targetQuarter)
            ->where('quarter_end', $targetQuarter)
            ->where('month_start', $monthStart)
            ->where('month_end', $monthEnd)
            ->max('version') ?? 0;
        
        $nextVersion = $maxVersion + 1;
        
        // Check if forcing to overwrite latest version
        if ($maxVersion > 0 && $force) {
            $this->info("Existing versions found: v1 to v{$maxVersion}");
            $this->info("Creating new version: v{$nextVersion}");
        } elseif ($maxVersion > 0 && !$force) {
            $this->warn("Report for {$targetYear} Q{$targetQuarter} already has v{$maxVersion}.");
            $this->info("Creating new version: v{$nextVersion}");
        } else {
            $this->info("Creating first version: v{$nextVersion}");
        }
        
        try {
            // Generate report data
            $this->info("Generating report data...");
            $report = $this->reportService->getCurrentReport($targetYear, $targetQuarter, $targetQuarter, null);
            
            if (empty($report['data'])) {
                $this->error("No data available for the specified period.");
                return 1;
            }
            
            // Note: --force flag is ignored for auto-save, always creates new version
            // This preserves version history for comparison in report history page
            
            // Clean up any partially saved records for this version (from previous failed attempts)
            // IMPORTANT: Must include soft-deleted records because MySQL unique constraint applies to ALL rows
            $partialCount = \App\Models\VaccinationReportSnapshot::withTrashed()
                ->where('year', $targetYear)
                ->where('quarter_start', $targetQuarter)
                ->where('quarter_end', $targetQuarter)
                ->where('month_start', $monthStart)
                ->where('month_end', $monthEnd)
                ->where('version', $nextVersion)
                ->count();
            
            if ($partialCount > 0) {
                $this->warn("Found {$partialCount} partial records (including soft-deleted) from previous failed attempt. Cleaning up...");
                // Use forceDelete to permanently remove soft-deleted records
                \App\Models\VaccinationReportSnapshot::withTrashed()
                    ->where('year', $targetYear)
                    ->where('quarter_start', $targetQuarter)
                    ->where('quarter_end', $targetQuarter)
                    ->where('month_start', $monthStart)
                    ->where('month_end', $monthEnd)
                    ->where('version', $nextVersion)
                    ->forceDelete();
                $this->info("Cleaned up {$partialCount} records.");
            }
            
            // Save each row in the table structure
            $this->info("Saving snapshot records...");
            $recordCount = 0;
            
            foreach ($report['data'] as $row) {
                $barangay = $row['barangay'] === 'TOTAL' ? null : $row['barangay'];
                
                // Save each vaccine's data
                if (isset($row['vaccines'])) {
                    foreach ($row['vaccines'] as $vaccineName => $vaccineData) {
                        \App\Models\VaccinationReportSnapshot::create([
                            'year' => $targetYear,
                            'quarter_start' => $targetQuarter,
                            'quarter_end' => $targetQuarter,
                            'month_start' => $monthStart,
                            'month_end' => $monthEnd,
                            'barangay' => $barangay,
                            'vaccine_name' => $vaccineName,
                            'version' => $nextVersion,
                            'male_count' => $vaccineData['male_count'] ?? 0,
                            'female_count' => $vaccineData['female_count'] ?? 0,
                            'total_count' => $vaccineData['total_count'] ?? 0,
                            'percentage' => $vaccineData['percentage'] ?? 0,
                            'eligible_population' => $row['eligible_population_under_1_year'] ?? 0,
                            'data_source' => 'calculated',
                            'created_by' => null,
                            'notes' => 'Auto-saved quarterly report'
                        ]);
                        $recordCount++;
                    }
                }
            }
            
            $this->info("✓ Report saved successfully!");
            $this->info("Version: v{$nextVersion}");
            $this->info("Total records saved: {$recordCount}");
            $this->info("Date range: {$report['date_range']}");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Failed to save report: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
