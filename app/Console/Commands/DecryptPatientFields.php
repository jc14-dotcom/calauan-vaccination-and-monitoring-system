<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class DecryptPatientFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:decrypt-fields 
                            {--dry-run : Show what would be decrypted without making changes}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decrypt encrypted patient fields (mother_name, father_name, place_of_birth, birth_height, birth_weight) and store as plain text';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('==============================================');
        $this->info('Patient Fields Decryption Tool');
        $this->info('==============================================');
        $this->newLine();

        // Get total patients
        $totalPatients = Patient::count();
        $this->info("Total patients in database: {$totalPatients}");
        $this->newLine();

        if ($totalPatients === 0) {
            $this->warn('No patients found in database.');
            return 0;
        }

        // Fields to decrypt
        $fieldsToDecrypt = ['mother_name', 'father_name', 'place_of_birth', 'birth_height', 'birth_weight'];
        
        $this->info('Fields that will be decrypted:');
        foreach ($fieldsToDecrypt as $field) {
            $this->line("  - {$field}");
        }
        $this->newLine();

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Confirmation
        if (!$force && !$dryRun) {
            if (!$this->confirm('This will decrypt encrypted fields and store them as plain text. Continue?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Processing patients...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar($totalPatients);
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;
        $alreadyPlainCount = 0;
        $errors = [];

        // Process each patient
        Patient::chunk(100, function ($patients) use (
            &$successCount, 
            &$errorCount, 
            &$alreadyPlainCount, 
            &$errors, 
            $fieldsToDecrypt, 
            $dryRun, 
            $progressBar
        ) {
            foreach ($patients as $patient) {
                try {
                    $updates = [];
                    $wasEncrypted = false;

                    foreach ($fieldsToDecrypt as $field) {
                        $value = $patient->getAttributes()[$field] ?? null;
                        
                        if ($value !== null && !empty($value)) {
                            // Try to detect if it's encrypted (Laravel encrypted format starts with "eyJpdiI6")
                            if (str_starts_with($value, 'eyJpdiI6') || str_starts_with($value, 'eyJ')) {
                                try {
                                    $decrypted = Crypt::decryptString($value);
                                    $updates[$field] = $decrypted;
                                    $wasEncrypted = true;
                                } catch (\Exception $e) {
                                    // If decryption fails, might already be plain text
                                    // Leave as-is
                                }
                            }
                        }
                    }

                    if (!empty($updates)) {
                        if (!$dryRun) {
                            // Use DB::table to bypass model casting and update raw values
                            DB::table('patients')
                                ->where('id', $patient->id)
                                ->update($updates);
                        }
                        $successCount++;
                    } else {
                        $alreadyPlainCount++;
                    }

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Patient ID {$patient->id}: {$e->getMessage()}";
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('==============================================');
        $this->info('SUMMARY');
        $this->info('==============================================');
        
        if ($dryRun) {
            $this->warn('DRY RUN - No actual changes were made');
        }
        
        $this->info("Total patients processed: {$totalPatients}");
        $this->info("Successfully decrypted: {$successCount}");
        $this->info("Already plain text: {$alreadyPlainCount}");
        
        if ($errorCount > 0) {
            $this->error("Errors encountered: {$errorCount}");
            $this->newLine();
            $this->error('Error details:');
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->line("  - {$error}");
            }
            if (count($errors) > 10) {
                $this->line("  ... and " . (count($errors) - 10) . " more errors");
            }
        }

        $this->newLine();

        if (!$dryRun && $successCount > 0) {
            $this->info('✓ Decryption completed successfully!');
            $this->warn('Remember to clear cache: php artisan cache:clear');
        } elseif ($dryRun) {
            $this->info('To perform actual decryption, run without --dry-run flag:');
            $this->line('  php artisan patients:decrypt-fields');
        }

        return 0;
    }
}
