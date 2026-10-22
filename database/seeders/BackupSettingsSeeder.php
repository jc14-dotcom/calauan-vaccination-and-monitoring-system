<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BackupSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('backup_settings')->updateOrInsert(
            ['id' => 1],
            [
                'schedule' => 'weekly',
                'enabled' => true,
                'last_backup_at' => null,
                'next_backup_at' => null,
                'retention_count' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
