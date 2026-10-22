<?php

namespace App\Console\Commands;

use App\Models\Patient;
use App\Models\PatientVaccineRecord;
use App\Models\Vaccine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackfillPatientVaccineRecords extends Command
{
    /**
     * Signature accepts an optional --vaccine_id flag to target a single vaccine.
     * Without it, all standard vaccines are processed.
     */
    protected $signature = 'vaccines:backfill-patient-records
                            {--vaccine_id= : Only backfill for this specific vaccine ID}';

    protected $description = 'Create missing PatientVaccineRecord rows for all standard (or a specific) vaccine.';

    public function handle(): int
    {
        $vaccineId = $this->option('vaccine_id');

        if ($vaccineId) {
            $vaccines = Vaccine::where('id', (int) $vaccineId)->get();
            if ($vaccines->isEmpty()) {
                $this->error("No vaccine found with ID {$vaccineId}.");
                return self::FAILURE;
            }
        } else {
            $vaccines = Vaccine::where('is_standard', true)->orderBy('sort_order')->get();
            if ($vaccines->isEmpty()) {
                $this->warn('No standard vaccines found. Run migrations and check is_standard flags.');
                return self::SUCCESS;
            }
        }

        $this->info("Backfilling patient vaccination records for {$vaccines->count()} vaccine(s)...");

        $bar = $this->output->createProgressBar($vaccines->count());
        $bar->start();

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($vaccines as $vaccine) {
            $created = 0;
            $skipped = 0;

            Patient::orderBy('id')->chunk(200, function ($patients) use ($vaccine, &$created, &$skipped) {
                foreach ($patients as $patient) {
                    $exists = PatientVaccineRecord::where('patient_id', $patient->id)
                        ->where('vaccine_id', $vaccine->id)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    PatientVaccineRecord::create([
                        'patient_id'  => $patient->id,
                        'vaccine_id'  => $vaccine->id,
                        'dose_1_date' => null,
                        'dose_2_date' => null,
                        'dose_3_date' => null,
                        'dose_4_date' => null,
                        'dose_5_date' => null,
                        'dose_6_date' => null,
                        'dose_7_date' => null,
                        'dose_8_date' => null,
                        'dose_9_date' => null,
                        'dose_10_date' => null,
                        'remarks'     => null,
                    ]);

                    $created++;
                }
            });

            $totalCreated += $created;
            $totalSkipped += $skipped;

            Log::info("Backfill complete for vaccine \"{$vaccine->vaccine_name}\": created {$created}, skipped {$skipped}.");
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Created: {$totalCreated} | Already existed (skipped): {$totalSkipped}");

        return self::SUCCESS;
    }
}
