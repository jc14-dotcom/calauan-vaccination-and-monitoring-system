<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaySeeder extends Seeder
{
    /**
     * Seed the barangays table with all 17 barangays of Calauan, Laguna.
     * 
     * Note: Kanluran has has_scheduled_vaccination = false because RHU is located there.
     * Patients from Kanluran go directly to RHU for vaccination, so no separate
     * vaccination schedule is created for Kanluran.
     */
    public function run(): void
    {
        $barangays = [
            ['name' => 'Balayhangin', 'code' => 'BLY', 'has_scheduled_vaccination' => true],
            ['name' => 'Bangyas', 'code' => 'BNG', 'has_scheduled_vaccination' => true],
            ['name' => 'Dayap', 'code' => 'DYP', 'has_scheduled_vaccination' => true],
            ['name' => 'Hanggan', 'code' => 'HNG', 'has_scheduled_vaccination' => true],
            ['name' => 'Imok', 'code' => 'IMK', 'has_scheduled_vaccination' => true],
            ['name' => 'Kanluran', 'code' => 'KNL', 'has_scheduled_vaccination' => false, 'notes' => 'RHU (Health Center) is located here. Kanluran patients go directly to RHU.'],
            ['name' => 'Lamot 1', 'code' => 'LM1', 'has_scheduled_vaccination' => true],
            ['name' => 'Lamot 2', 'code' => 'LM2', 'has_scheduled_vaccination' => true],
            ['name' => 'Limao', 'code' => 'LMO', 'has_scheduled_vaccination' => true],
            ['name' => 'Mabacan', 'code' => 'MBC', 'has_scheduled_vaccination' => true],
            ['name' => 'Masiit', 'code' => 'MST', 'has_scheduled_vaccination' => true],
            ['name' => 'Paliparan', 'code' => 'PLP', 'has_scheduled_vaccination' => true],
            ['name' => 'Perez', 'code' => 'PRZ', 'has_scheduled_vaccination' => true],
            ['name' => 'Prinza', 'code' => 'PNZ', 'has_scheduled_vaccination' => true],
            ['name' => 'San Isidro', 'code' => 'SNI', 'has_scheduled_vaccination' => true],
            ['name' => 'Santo Tomas', 'code' => 'SNT', 'has_scheduled_vaccination' => true],
            ['name' => 'Silangan', 'code' => 'SLN', 'has_scheduled_vaccination' => true],
        ];

        foreach ($barangays as $barangay) {
            DB::table('barangays')->updateOrInsert(
                ['name' => $barangay['name']], // Match by name
                [
                    'code' => $barangay['code'],
                    'has_scheduled_vaccination' => $barangay['has_scheduled_vaccination'],
                    'notes' => $barangay['notes'] ?? null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
