<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed vaccine config columns from the existing static VaccineConfig data.
     * This is idempotent — safe to re-run.
     *
     * Vaccine names here are matched against the vaccines.vaccine_name column.
     * Both 'Tetanus Diptheria' and 'Tetanus Diphtheria' spellings are attempted.
     */
    public function up(): void
    {
        $staticConfig = $this->getStaticConfig();

        foreach ($staticConfig as $sortOrder => $entry) {
            // Match vaccine by name (try exact name from config, then any alternative)
            $vaccine = null;
            foreach ($entry['db_name_candidates'] as $candidateName) {
                $vaccine = DB::table('vaccines')
                    ->where('vaccine_name', $candidateName)
                    ->first();
                if ($vaccine) {
                    break;
                }
            }

            if (!$vaccine) {
                // Vaccine does not exist in inventory yet — skip silently
                continue;
            }

            // Update new config columns on the vaccine row
            DB::table('vaccines')
                ->where('id', $vaccine->id)
                ->update([
                    'total_doses'           => $entry['total_doses'],
                    'target_age_group'      => $entry['target_age_group'],
                    'acronym'               => $entry['acronym'],
                    'is_standard'           => true,
                    'is_active_in_reports'  => true,
                    'sort_order'            => $sortOrder,
                    'updated_at'            => now(),
                ]);

            // Upsert dose config rows
            foreach ($entry['recommended_ages'] as $doseNumber => $ages) {
                $existing = DB::table('vaccine_dose_configs')
                    ->where('vaccine_id', $vaccine->id)
                    ->where('dose_number', $doseNumber)
                    ->first();

                if ($existing) {
                    DB::table('vaccine_dose_configs')
                        ->where('id', $existing->id)
                        ->update([
                            'min_age_months' => $ages['min'],
                            'max_age_months' => $ages['max'],
                            'updated_at'     => now(),
                        ]);
                } else {
                    DB::table('vaccine_dose_configs')->insert([
                        'vaccine_id'      => $vaccine->id,
                        'dose_number'     => $doseNumber,
                        'min_age_months'  => $ages['min'],
                        'max_age_months'  => $ages['max'],
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }

        // Flush the vaccine config cache so VaccineConfig reads fresh data
        \Illuminate\Support\Facades\Cache::forget('vaccine_config');

        // Handle vaccines stored with the same name (Grade 1 vs Grade 7 Measles Containing)
        $this->handleDuplicateNameVaccines();

        \Illuminate\Support\Facades\Cache::forget('vaccine_config');
    }

    public function down(): void
    {
        // Remove all dose config rows for the 11 standard vaccines
        $names = [
            'BCG', 'Hepatitis B', 'Pentavalent', 'Oral Polio', 'Inactivated Polio',
            'Pneumococcal Conjugate', 'Measles, Mumps, Rubella',
            'Measles Containing (Grade 1)', 'Measles Containing (Grade 7)',
            'Tetanus Diptheria', 'Tetanus Diphtheria', 'Human Papillomavirus',
        ];

        $vaccineIds = DB::table('vaccines')
            ->whereIn('vaccine_name', $names)
            ->pluck('id');

        DB::table('vaccine_dose_configs')
            ->whereIn('vaccine_id', $vaccineIds)
            ->delete();

        // Reset new columns to defaults
        DB::table('vaccines')
            ->whereIn('id', $vaccineIds)
            ->update([
                'total_doses'          => 1,
                'target_age_group'     => 'under_1_year',
                'acronym'              => null,
                'is_standard'          => false,
                'is_active_in_reports' => false,
                'sort_order'           => 99,
                'updated_at'           => now(),
            ]);

        \Illuminate\Support\Facades\Cache::forget('vaccine_config');
    }

    /**
     * Inline static config — intentionally not calling VaccineConfig::getDoseConfiguration()
     * to avoid circular dependency issues during migration. Sort order = array key.
     */
    private function getStaticConfig(): array
    {
        return [
            1 => [
                'db_name_candidates' => ['BCG'],
                'total_doses'        => 1,
                'target_age_group'   => 'under_1_year',
                'acronym'            => 'BCG',
                'recommended_ages'   => [
                    1 => ['min' => 0, 'max' => 1],
                ],
            ],
            2 => [
                'db_name_candidates' => ['Hepatitis B'],
                'total_doses'        => 1,
                'target_age_group'   => 'under_1_year',
                'acronym'            => 'HepB',
                'recommended_ages'   => [
                    1 => ['min' => 0, 'max' => 0.5],
                ],
            ],
            3 => [
                'db_name_candidates' => ['Pentavalent'],
                'total_doses'        => 3,
                'target_age_group'   => 'under_1_year',
                'acronym'            => 'DPT-HIB-HepB',
                'recommended_ages'   => [
                    1 => ['min' => 1.5, 'max' => 2.5],
                    2 => ['min' => 2.5, 'max' => 3.5],
                    3 => ['min' => 3.5, 'max' => 4.5],
                ],
            ],
            4 => [
                'db_name_candidates' => ['Oral Polio'],
                'total_doses'        => 3,
                'target_age_group'   => 'under_1_year',
                'acronym'            => 'OPV',
                'recommended_ages'   => [
                    1 => ['min' => 1.5, 'max' => 2.5],
                    2 => ['min' => 2.5, 'max' => 3.5],
                    3 => ['min' => 3.5, 'max' => 4.5],
                ],
            ],
            5 => [
                'db_name_candidates' => ['Inactivated Polio'],
                'total_doses'        => 2,
                'target_age_group'   => 'under_1_year',
                'acronym'            => 'IPV',
                'recommended_ages'   => [
                    1 => ['min' => 3,  'max' => 4],
                    2 => ['min' => 9,  'max' => 10],
                ],
            ],
            6 => [
                'db_name_candidates' => ['Pneumococcal Conjugate'],
                'total_doses'        => 3,
                'target_age_group'   => 'under_1_year',
                'acronym'            => 'PCV',
                'recommended_ages'   => [
                    1 => ['min' => 1.5, 'max' => 2.5],
                    2 => ['min' => 2.5, 'max' => 3.5],
                    3 => ['min' => 3.5, 'max' => 4.5],
                ],
            ],
            7 => [
                'db_name_candidates' => ['Measles, Mumps, Rubella'],
                'total_doses'        => 2,
                'target_age_group'   => '0_12_months',
                'acronym'            => 'MMR',
                'recommended_ages'   => [
                    1 => ['min' => 9,  'max' => 12],
                    2 => ['min' => 12, 'max' => 15],
                ],
            ],
            8 => [
                'db_name_candidates' => ['Measles Containing (Grade 1)'],
                'total_doses'        => 1,
                'target_age_group'   => 'grade_1',
                'acronym'            => 'MCV1',
                'recommended_ages'   => [
                    1 => ['min' => 72, 'max' => 84],
                ],
            ],
            9 => [
                'db_name_candidates' => ['Measles Containing (Grade 7)'],
                'total_doses'        => 2,
                'target_age_group'   => 'grade_7',
                'acronym'            => 'MCV2',
                'recommended_ages'   => [
                    1 => ['min' => 144, 'max' => 156],
                    2 => ['min' => 144, 'max' => 156],
                ],
            ],
            10 => [
                // Handles both the typo (Diptheria) and correct spelling in DB
                'db_name_candidates' => ['Tetanus Diptheria', 'Tetanus Diphtheria'],
                'total_doses'        => 2,
                'target_age_group'   => 'grade_7',
                'acronym'            => 'TD',
                'recommended_ages'   => [
                    1 => ['min' => 144, 'max' => 156],
                    2 => ['min' => 144, 'max' => 156],
                ],
            ],
            11 => [
                'db_name_candidates' => ['Human Papillomavirus'],
                'total_doses'        => 2,
                'target_age_group'   => 'grade_7',
                'acronym'            => 'HPV',
                'recommended_ages'   => [
                    1 => ['min' => 108, 'max' => 132],
                    2 => ['min' => 108, 'max' => 132],
                ],
            ],
        ];
    }

    /**
     * Handle vaccines that share the same name in the DB (e.g., both "Measles Containing").
     * Ordered by id ascending: lower id = Grade 1, higher id = Grade 7.
     */
    private function handleDuplicateNameVaccines(): void
    {
        $measlesVaccines = DB::table('vaccines')
            ->where('vaccine_name', 'like', 'Measles Containing%')
            ->orderBy('id')
            ->get();

        if ($measlesVaccines->count() < 2) {
            return; // Only one or zero — skip, nothing to disambiguate
        }

        // Assign Grade 1 config to the lower-id vaccine
        $grade1 = $measlesVaccines->first();
        DB::table('vaccines')->where('id', $grade1->id)->update([
            'total_doses'          => 1,
            'target_age_group'     => 'grade_1',
            'acronym'              => 'MCV1',
            'is_standard'          => true,
            'is_active_in_reports' => true,
            'sort_order'           => 8,
            'updated_at'           => now(),
        ]);
        DB::table('vaccine_dose_configs')->updateOrInsert(
            ['vaccine_id' => $grade1->id, 'dose_number' => 1],
            ['min_age_months' => 72, 'max_age_months' => 84, 'created_at' => now(), 'updated_at' => now()]
        );

        // Assign Grade 7 config to the higher-id vaccine
        $grade7 = $measlesVaccines->skip(1)->first();
        DB::table('vaccines')->where('id', $grade7->id)->update([
            'total_doses'          => 2,
            'target_age_group'     => 'grade_7',
            'acronym'              => 'MCV2',
            'is_standard'          => true,
            'is_active_in_reports' => true,
            'sort_order'           => 9,
            'updated_at'           => now(),
        ]);
        foreach ([1, 2] as $dose) {
            DB::table('vaccine_dose_configs')->updateOrInsert(
                ['vaccine_id' => $grade7->id, 'dose_number' => $dose],
                ['min_age_months' => 144, 'max_age_months' => 156, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
};