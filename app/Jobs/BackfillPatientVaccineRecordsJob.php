<?php

namespace App\Jobs;

use App\Models\Patient;
use App\Models\PatientVaccineRecord;
use App\Models\Vaccine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class BackfillPatientVaccineRecordsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Maximum attempts before the job is marked as failed.
     */
    public int $tries = 3;

    public function __construct(private readonly int $vaccineId)
    {
    }

    /**
     * Iterate all patients in chunks and create a PatientVaccineRecord for
     * any patient who doesn't yet have one for this vaccine.
     */
    public function handle(): void
    {
        $vaccine = Vaccine::find($this->vaccineId);

        if (!$vaccine) {
            Log::warning("BackfillPatientVaccineRecordsJob: vaccine ID {$this->vaccineId} not found.");
            return;
        }

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

        Log::info("BackfillPatientVaccineRecordsJob: vaccine \"{$vaccine->vaccine_name}\" — created {$created}, skipped {$skipped} existing.");
    }
}
