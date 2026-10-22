<?php

namespace App\Config;

use Illuminate\Support\Facades\Cache;

class VaccineConfig
{
    /**
     * Get complete vaccine dose configuration.
     *
     * Reads from the vaccines + vaccine_dose_configs DB tables (cached for 1 hour).
     * Falls back to the static configuration if the DB is empty or unavailable
     * (e.g., during initial migrations).
     *
     * Return shape is identical to the original static array so all callers
     * (VaccinationReportService, VaccineAgeFilterService, etc.) are unaffected.
     *
     * @return array
     */
    public static function getDoseConfiguration(): array
    {
        try {
            return Cache::remember('vaccine_config', 3600, function () {
                $vaccines = \App\Models\Vaccine::with(['doseConfigs' => function ($q) {
                    $q->orderBy('dose_number');
                }])
                ->where('is_active_in_reports', true)
                ->orderBy('sort_order')
                ->get();

                if ($vaccines->isEmpty()) {
                    return self::getStaticConfiguration();
                }

                $config = [];
                foreach ($vaccines as $vaccine) {
                    $recommendedAges = [];
                    foreach ($vaccine->doseConfigs as $doseConfig) {
                        $recommendedAges[(int) $doseConfig->dose_number] = [
                            'min' => (float) $doseConfig->min_age_months,
                            'max' => (float) $doseConfig->max_age_months,
                        ];
                    }

                    $config[$vaccine->vaccine_name] = [
                        'total_doses'      => (int) ($vaccine->total_doses ?? 1),
                        'target_age_group' => $vaccine->target_age_group ?? 'under_1_year',
                        'acronym'          => $vaccine->acronym ?? $vaccine->vaccine_name,
                        'full_name'        => $vaccine->vaccine_name,
                        'recommended_ages' => $recommendedAges,
                    ];
                }

                return $config;
            });
        } catch (\Exception $e) {
            // Fall back to the static array (during migrations or DB unavailability)
            return self::getStaticConfiguration();
        }
    }

    /**
     * Original static configuration used as a fallback.
     * Do NOT call this directly in application code — use getDoseConfiguration().
     */
    private static function getStaticConfiguration(): array
    {
        return [
            'BCG' => [
                'total_doses' => 1,
                'target_age_group' => 'under_1_year',
                'acronym' => 'BCG',
                'full_name' => 'BCG Vaccine',
                'recommended_ages' => [
                    1 => ['min' => 0, 'max' => 1], // At birth to 1 month
                ],
            ],
            'Hepatitis B' => [
                'total_doses' => 1,
                'target_age_group' => 'under_1_year',
                'acronym' => 'HepB',
                'full_name' => 'Hepatitis B',
                'recommended_ages' => [
                    1 => ['min' => 0, 'max' => 0.5], // Within 24 hours (0.5 month)
                ],
            ],
            'Pentavalent' => [
                'total_doses' => 3,
                'target_age_group' => 'under_1_year',
                'acronym' => 'DPT-HIB-HepB',
                'full_name' => 'Pentavalent',
                'recommended_ages' => [
                    1 => ['min' => 1.5, 'max' => 2.5], // 6 weeks (1.5 months)
                    2 => ['min' => 2.5, 'max' => 3.5], // 10 weeks (2.5 months)
                    3 => ['min' => 3.5, 'max' => 4.5], // 14 weeks (3.5 months)
                ],
            ],
            'Oral Polio' => [
                'total_doses' => 3,
                'target_age_group' => 'under_1_year',
                'acronym' => 'OPV',
                'full_name' => 'Oral Polio',
                'recommended_ages' => [
                    1 => ['min' => 1.5, 'max' => 2.5], // 6 weeks
                    2 => ['min' => 2.5, 'max' => 3.5], // 10 weeks
                    3 => ['min' => 3.5, 'max' => 4.5], // 14 weeks
                ],
            ],
            'Inactivated Polio' => [
                'total_doses' => 2,
                'target_age_group' => 'under_1_year',
                'acronym' => 'IPV',
                'full_name' => 'Inactivated Polio',
                'recommended_ages' => [
                    1 => ['min' => 3, 'max' => 4], // 14 weeks (3.5 months)
                    2 => ['min' => 9, 'max' => 10], // 9 months
                ],
            ],
            'Pneumococcal Conjugate' => [
                'total_doses' => 3,
                'target_age_group' => 'under_1_year',
                'acronym' => 'PCV',
                'full_name' => 'Pneumococcal Conjugate',
                'recommended_ages' => [
                    1 => ['min' => 1.5, 'max' => 2.5], // 6 weeks
                    2 => ['min' => 2.5, 'max' => 3.5], // 10 weeks
                    3 => ['min' => 3.5, 'max' => 4.5], // 14 weeks
                ],
            ],
            'Measles, Mumps, Rubella' => [
                'total_doses' => 2,
                'target_age_group' => '0_12_months',
                'acronym' => 'MMR',
                'full_name' => 'Measles, Mumps, Rubella',
                'recommended_ages' => [
                    1 => ['min' => 9, 'max' => 12], // 9 months
                    2 => ['min' => 12, 'max' => 15], // 12-15 months
                ],
            ],
            'Measles Containing (Grade 1)' => [
                'total_doses' => 1,
                'target_age_group' => 'grade_1',
                'acronym' => 'MCV1',
                'full_name' => 'Measles Containing (Grade 1)',
                'recommended_ages' => [
                    1 => ['min' => 72, 'max' => 84], // 6-7 years old (72-84 months)
                ],
            ],
            'Measles Containing (Grade 7)' => [
                'total_doses' => 2,
                'target_age_group' => 'grade_7',
                'acronym' => 'MCV2',
                'full_name' => 'Measles Containing (Grade 7)',
                'recommended_ages' => [
                    1 => ['min' => 144, 'max' => 156], // 12-13 years old (144-156 months)
                    2 => ['min' => 144, 'max' => 156], // Same age range for 2nd dose
                ],
            ],
            'Tetanus Diptheria' => [
                'total_doses' => 2,
                'target_age_group' => 'grade_7',
                'acronym' => 'TD',
                'full_name' => 'Tetanus Diptheria',
                'recommended_ages' => [
                    1 => ['min' => 144, 'max' => 156], // 12-13 years old
                    2 => ['min' => 144, 'max' => 156],
                ],
            ],
            'Human Papillomavirus' => [
                'total_doses' => 2,
                'target_age_group' => 'grade_7',
                'acronym' => 'HPV',
                'full_name' => 'Human Papillomavirus',
                'recommended_ages' => [
                    1 => ['min' => 108, 'max' => 132], // 9-11 years old (Grade 4-5 typically)
                    2 => ['min' => 108, 'max' => 132],
                ],
            ],
        ];
    }

    /**
     * Get vaccines required for Fully Immunized Child (FIC)
     * FIC = Children 0-12 months who completed required infant vaccines
     * 
     * @return array [vaccine_name => required_doses]
     */
    public static function getFICVaccines()
    {
        return [
            'BCG' => 1,
            'Hepatitis B Vaccine' => 1,
            'Pentavalent Vaccine' => 3,
            'Oral Polio Vaccine' => 3,
            'Measles, Mumps, Rubella Vaccine' => 2,
        ];
    }

    /**
     * Get vaccines required for Completely Immunized Child (CIC)
     * CIC = School-aged children (13-23 months) who completed all vaccines including school-based
     * 
     * @return array [vaccine_name => required_doses]
     */
    public static function getCICVaccines()
    {
        return [
            // All FIC vaccines
            'BCG' => 1,
            'Hepatitis B Vaccine' => 1,
            'Pentavalent Vaccine' => 3,
            'Oral Polio Vaccine' => 3,
            'Measles, Mumps, Rubella Vaccine' => 2,
            // Plus school vaccines
            'Measles Containing Vaccine (Grade 1)' => 1,
            'Measles Containing Vaccine (Grade 7)' => 2,
            'Tetanus Diptheria' => 2,
            'Human Papillomavirus Vaccine' => 2,
        ];
    }

    /**
     * Get dose count for a specific vaccine
     * 
     * @param string $vaccineName
     * @return int
     */
    public static function getTotalDoses($vaccineName)
    {
        $config = self::getDoseConfiguration();
        return $config[$vaccineName]['total_doses'] ?? 1;
    }

    /**
     * Get acronym for a vaccine
     * 
     * @param string $vaccineName
     * @return string
     */
    public static function getAcronym($vaccineName)
    {
        $config = self::getDoseConfiguration();
        return $config[$vaccineName]['acronym'] ?? $vaccineName;
    }

    /**
     * Get target age group for a vaccine
     * 
     * @param string $vaccineName
     * @return string
     */
    public static function getTargetAgeGroup($vaccineName)
    {
        $config = self::getDoseConfiguration();
        return $config[$vaccineName]['target_age_group'] ?? 'under_1_year';
    }

    /**
     * Check if a dose is considered "catch-up" (given late)
     * 
     * @param string $vaccineName
     * @param int $doseNumber
     * @param int $patientAgeInMonths
     * @return bool
     */
    public static function isCatchUpDose($vaccineName, $doseNumber, $patientAgeInMonths)
    {
        $config = self::getDoseConfiguration();
        
        if (!isset($config[$vaccineName]['recommended_ages'][$doseNumber])) {
            return false;
        }
        
        $recommendedAge = $config[$vaccineName]['recommended_ages'][$doseNumber];
        
        // If patient age is beyond the max recommended age, it's a catch-up dose
        return $patientAgeInMonths > $recommendedAge['max'];
    }

    /**
     * Get all vaccine names
     * 
     * @return array
     */
    public static function getAllVaccineNames()
    {
        return array_keys(self::getDoseConfiguration());
    }

    /**
     * Get vaccines by target age group
     * 
     * @param string $ageGroup (under_1_year, 0_12_months, grade_1, grade_7)
     * @return array
     */
    public static function getVaccinesByAgeGroup($ageGroup)
    {
        $config = self::getDoseConfiguration();
        $vaccines = [];
        
        foreach ($config as $name => $data) {
            if ($data['target_age_group'] === $ageGroup) {
                $vaccines[$name] = $data;
            }
        }
        
        return $vaccines;
    }
}
