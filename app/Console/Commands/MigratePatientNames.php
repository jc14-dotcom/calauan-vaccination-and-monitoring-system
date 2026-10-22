<?php

namespace App\Console\Commands;

use App\Models\Patient;
use Illuminate\Console\Command;

/**
 * Command to migrate existing patient names from the single 'name' field
 * to the new separate name fields (first_name, middle_initial, last_name, suffix).
 * 
 * This command parses the existing 'name' field and populates the new fields.
 * It should be run once after the migration adds the new columns.
 */
class MigratePatientNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:migrate-names 
                            {--dry-run : Run without making changes to see what would happen}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing patient names to separate name fields (first_name, middle_initial, last_name, suffix)';

    /**
     * Known suffixes to detect
     */
    protected array $suffixes = ['Jr.', 'Jr', 'Sr.', 'Sr', 'II', 'III', 'IV', 'V'];

    /**
     * Compound surname prefixes (common in Filipino/Spanish names)
     */
    protected array $compoundPrefixes = ['dela', 'de la', 'delos', 'de los', 'del', 'de', 'las', 'los', 'san', 'santa', 'santo', 'sta', 'sto'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('=== Patient Name Migration Tool ===');
        $this->newLine();
        
        // Count patients needing migration
        $patientsToMigrate = Patient::whereNull('first_name')
            ->orWhereNull('last_name')
            ->count();
        
        $this->info("Found {$patientsToMigrate} patients that need name migration.");
        
        if ($patientsToMigrate === 0) {
            $this->info('All patients already have separate name fields. Nothing to do.');
            return Command::SUCCESS;
        }
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made.');
        } elseif (!$this->option('force')) {
            if (!$this->confirm('Do you want to proceed with migrating patient names?')) {
                $this->info('Migration cancelled.');
                return Command::SUCCESS;
            }
        }
        
        $progressBar = $this->output->createProgressBar($patientsToMigrate);
        $progressBar->start();
        
        $migrated = 0;
        $failed = 0;
        $skipped = 0;
        $failedNames = [];
        
        // Process patients in chunks
        Patient::whereNull('first_name')
            ->orWhereNull('last_name')
            ->chunk(100, function ($patients) use (&$migrated, &$failed, &$skipped, &$failedNames, $progressBar, $dryRun) {
                foreach ($patients as $patient) {
                    $progressBar->advance();
                    
                    // Skip if name is empty
                    if (empty($patient->name)) {
                        $skipped++;
                        continue;
                    }
                    
                    // Skip if already has the new fields
                    if (!empty($patient->first_name) && !empty($patient->last_name)) {
                        $skipped++;
                        continue;
                    }
                    
                    // Parse the name
                    $parsed = $this->parseName($patient->name);
                    
                    if ($parsed === null) {
                        $failed++;
                        $failedNames[] = [
                            'id' => $patient->id,
                            'name' => $patient->name,
                            'reason' => 'Could not parse name'
                        ];
                        continue;
                    }
                    
                    if (!$dryRun) {
                        try {
                            $patient->update([
                                'first_name' => $parsed['first_name'],
                                'middle_initial' => $parsed['middle_initial'],
                                'last_name' => $parsed['last_name'],
                                'suffix' => $parsed['suffix'],
                            ]);
                            $migrated++;
                        } catch (\Exception $e) {
                            $failed++;
                            $failedNames[] = [
                                'id' => $patient->id,
                                'name' => $patient->name,
                                'reason' => $e->getMessage()
                            ];
                        }
                    } else {
                        // Dry run - just count
                        $migrated++;
                        
                        // Show parsed result for first 5
                        if ($migrated <= 5) {
                            $this->newLine();
                            $this->line("  [{$patient->id}] \"{$patient->name}\" → ");
                            $this->line("      First: {$parsed['first_name']}, MI: " . ($parsed['middle_initial'] ?? '-') . 
                                       ", Last: {$parsed['last_name']}, Suffix: " . ($parsed['suffix'] ?? '-'));
                        }
                    }
                }
            });
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('=== Migration Summary ===');
        $this->info("Migrated: {$migrated}");
        $this->info("Skipped: {$skipped}");
        $this->warn("Failed: {$failed}");
        
        if (!empty($failedNames) && $failed <= 20) {
            $this->newLine();
            $this->warn('Failed names:');
            foreach ($failedNames as $failedName) {
                $this->line("  [{$failedName['id']}] \"{$failedName['name']}\" - {$failedName['reason']}");
            }
        }
        
        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }
        
        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Parse a full name into components.
     * 
     * Expected formats:
     * - "FirstName LastName"
     * - "FirstName M.I. LastName"
     * - "FirstName SecondName M.I. LastName"
     * - "FirstName M.I. LastName Jr."
     * - "FirstName SecondName LastName"
     * - "FirstName M.I. Dela Cruz" (compound surname)
     * 
     * @param string $fullName
     * @return array|null
     */
    protected function parseName(string $fullName): ?array
    {
        $parts = preg_split('/\s+/', trim($fullName));
        
        if (count($parts) < 2) {
            // Can't parse a single word as first + last
            return null;
        }
        
        $result = [
            'first_name' => null,
            'middle_initial' => null,
            'last_name' => null,
            'suffix' => null,
        ];
        
        // Check if last part is a suffix
        $lastPart = end($parts);
        if (in_array($lastPart, $this->suffixes)) {
            $result['suffix'] = $lastPart;
            array_pop($parts);
        }
        
        // Now we should have at least 2 parts for first and last name
        if (count($parts) < 2) {
            return null;
        }
        
        // Check for compound surnames (Dela Cruz, De Los Santos, etc.)
        // Work backwards from the end to find compound surname
        $lastNameParts = [];
        $foundCompoundPrefix = false;
        
        // Start from the end and work backwards
        while (count($parts) > 1) {
            $currentPart = end($parts);
            $lowerPart = strtolower($currentPart);
            
            if ($foundCompoundPrefix) {
                // We already have the surname, stop
                break;
            }
            
            // Check if this is a compound prefix
            if (in_array($lowerPart, $this->compoundPrefixes)) {
                array_unshift($lastNameParts, array_pop($parts));
                $foundCompoundPrefix = true;
                // Continue to check for multi-word prefixes like "De La"
                continue;
            }
            
            // If we haven't collected any last name parts yet, this is the last name
            if (empty($lastNameParts)) {
                array_unshift($lastNameParts, array_pop($parts));
                continue;
            }
            
            // If the previous part we collected was a compound prefix, keep adding
            if ($foundCompoundPrefix) {
                // Check if this is also part of compound (e.g., "De" in "De La")
                if (in_array($lowerPart, $this->compoundPrefixes)) {
                    array_unshift($lastNameParts, array_pop($parts));
                    continue;
                }
            }
            
            // We have the last name, stop
            break;
        }
        
        $result['last_name'] = implode(' ', $lastNameParts);
        
        // Check if the now-last part is a middle initial
        if (count($parts) >= 2) {
            $potentialMI = end($parts);
            // Match: single letter OR letter with period (B, B., T, T.)
            if (preg_match('/^[A-Za-z]\.?$/', $potentialMI)) {
                $result['middle_initial'] = rtrim(array_pop($parts), '.');
            }
        }
        
        // Everything remaining is first name(s)
        $result['first_name'] = implode(' ', $parts);
        
        // Validate we have the essentials
        if (empty($result['first_name']) || empty($result['last_name'])) {
            return null;
        }
        
        return $result;
    }
}
