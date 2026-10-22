<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Vaccine;
use App\Models\PatientVaccineRecord;
use Illuminate\Support\Facades\Log;

class PatientVaccinationService
{
    /**
     * Create standard vaccination records for a new patient
     */
    public function createStandardVaccinationRecords(Patient $patient): void
    {
        // Get all standard vaccines in the correct order
        $standardVaccines = $this->getStandardVaccines();

        foreach ($standardVaccines as $vaccine) {
            try {
                // firstOrCreate is atomic and handles the unique constraint gracefully
                PatientVaccineRecord::firstOrCreate(
                    [
                        'patient_id' => $patient->id,
                        'vaccine_id' => $vaccine->id,
                    ],
                    [
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
                    ]
                );
            } catch (\Exception $e) {
                // Log but continue — one failed record should not block the rest
                Log::warning("Skipped vaccine {$vaccine->id} for patient {$patient->id}: " . $e->getMessage());
            }
        }

        Log::info("Standard vaccination records seeded for patient {$patient->id}");
    }

    /**
     * Get all standard vaccines ordered by sort_order.
     * Phase 2: reads from the DB (is_standard = true) instead of a hardcoded list.
     */
    private function getStandardVaccines(): \Illuminate\Support\Collection
    {
        return Vaccine::where('is_standard', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Fix existing patients who don't have complete vaccination records
     */
    public function fixIncompletePatientRecords(): array
    {
        $results = [
            'patients_processed' => 0,
            'records_created' => 0,
            'errors' => []
        ];

        try {
            $allPatients = Patient::all();
            $standardVaccines = $this->getStandardVaccines();

            foreach ($allPatients as $patient) {
                $results['patients_processed']++;
                
                foreach ($standardVaccines as $vaccine) {
                    $existingRecord = PatientVaccineRecord::where('patient_id', $patient->id)
                                                        ->where('vaccine_id', $vaccine->id)
                                                        ->first();

                    if (!$existingRecord) {
                        try {
                            PatientVaccineRecord::create([
                                'patient_id' => $patient->id,
                                'vaccine_id' => $vaccine->id,
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
                                'remarks' => null,
                            ]);
                            $results['records_created']++;
                        } catch (\Exception $e) {
                            $results['errors'][] = "Failed to create record for patient {$patient->id}, vaccine {$vaccine->id}: " . $e->getMessage();
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $results['errors'][] = "General error: " . $e->getMessage();
        }

        return $results;
    }
}
