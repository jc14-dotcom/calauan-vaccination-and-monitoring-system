<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VaccineSeeder extends Seeder
{
    /**
     * Seed the standard vaccine catalog, dose settings, and zero stock.
     */
    public function run(): void
    {
        $vaccines = [
            [
                'vaccine_name' => 'BCG',
                'doses_description' => 'Pagkapanganak',
                'total_doses' => 1,
                'target_age_group' => 'under_1_year',
                'acronym' => 'BCG',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 1,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 5,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[0, 1]],
            ],
            [
                'vaccine_name' => 'Hepatitis B',
                'doses_description' => 'Pagkapanganak',
                'total_doses' => 1,
                'target_age_group' => 'under_1_year',
                'acronym' => 'HepB',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 2,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 10,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[0, 0.5]],
            ],
            [
                'vaccine_name' => 'Pentavalent',
                'doses_description' => '1 1/2, 2 1/2, 3 1/2 Buwan',
                'total_doses' => 3,
                'target_age_group' => 'under_1_year',
                'acronym' => 'DPT-HIB-HepB',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 3,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 5,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[1.5, 2.5], [2.5, 3.5], [3.5, 4.5]],
            ],
            [
                'vaccine_name' => 'Oral Polio',
                'doses_description' => '1 1/2, 2 1/2, 3 1/2 Buwan',
                'total_doses' => 3,
                'target_age_group' => 'under_1_year',
                'acronym' => 'OPV',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 4,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 10,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[1.5, 2.5], [2.5, 3.5], [3.5, 4.5]],
            ],
            [
                'vaccine_name' => 'Inactivated Polio',
                'doses_description' => '3 1/2 & 9 Buwan',
                'total_doses' => 2,
                'target_age_group' => 'under_1_year',
                'acronym' => 'IPV',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 5,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 10,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[3, 4], [9, 10]],
            ],
            [
                'vaccine_name' => 'Pneumococcal Conjugate',
                'doses_description' => '1 1/2, 2 1/2, 3 1/2 Buwan',
                'total_doses' => 3,
                'target_age_group' => 'under_1_year',
                'acronym' => 'PCV',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 6,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 5,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[1.5, 2.5], [2.5, 3.5], [3.5, 4.5]],
            ],
            [
                'vaccine_name' => 'Measles, Mumps, Rubella',
                'doses_description' => '9 Buwan & 1 Taon',
                'total_doses' => 2,
                'target_age_group' => '0_12_months',
                'acronym' => 'MMR',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 7,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 10,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[9, 12], [12, 15]],
            ],
            [
                'vaccine_name' => 'Measles Containing',
                'doses_description' => 'Grade 1',
                'total_doses' => 1,
                'target_age_group' => 'grade_1',
                'acronym' => 'MCV1',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 8,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 10,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[72, 84]],
            ],
            [
                'vaccine_name' => 'Measles Containing',
                'doses_description' => 'Grade 7',
                'total_doses' => 2,
                'target_age_group' => 'grade_7',
                'acronym' => 'MCV2',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 9,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 10,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[144, 156], [144, 156]],
            ],
            [
                'vaccine_name' => 'Tetanus Diphtheria',
                'doses_description' => 'Grade 1 & 7',
                'total_doses' => 2,
                'target_age_group' => 'grade_7',
                'acronym' => 'TD',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 10,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 1,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[144, 156], [144, 156]],
            ],
            [
                'vaccine_name' => 'Human Papillomavirus',
                'doses_description' => 'Grade 4 - (Babae) 9-14 Taon Gulang',
                'total_doses' => 2,
                'target_age_group' => 'grade_7',
                'acronym' => 'HPV',
                'is_standard' => true,
                'is_active_in_reports' => true,
                'sort_order' => 11,
                'stocks' => 0,
                'available_bottles' => 0,
                'doses_per_bottle' => 1,
                'doses_used_from_current_bottles' => 0,
                'dose_configs' => [[108, 132], [108, 132]],
            ],
        ];

        DB::transaction(function () use ($vaccines): void {
            foreach ($vaccines as $vaccine) {
                $doseConfigs = $vaccine['dose_configs'];
                unset($vaccine['dose_configs']);

                $vaccine['updated_at'] = now();
                $existing = DB::table('vaccines')
                    ->where('vaccine_name', $vaccine['vaccine_name'])
                    ->where('sort_order', $vaccine['sort_order'])
                    ->first();

                if ($existing) {
                    DB::table('vaccines')->where('id', $existing->id)->update($vaccine);
                    $vaccineId = $existing->id;
                } else {
                    $vaccine['created_at'] = now();
                    $vaccineId = DB::table('vaccines')->insertGetId($vaccine);
                }

                DB::table('vaccine_dose_configs')->where('vaccine_id', $vaccineId)->delete();

                foreach ($doseConfigs as $doseNumber => [$minAge, $maxAge]) {
                    DB::table('vaccine_dose_configs')->insert([
                        'vaccine_id' => $vaccineId,
                        'dose_number' => $doseNumber + 1,
                        'min_age_months' => $minAge,
                        'max_age_months' => $maxAge,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        $this->command?->info('Seeded ' . count($vaccines) . ' standard vaccines with zero stock.');
    }
}
