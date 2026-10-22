<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all patient IDs for parent 44
        $patientIds = DB::table('patients')
            ->where('parent_id', 44)
            ->pluck('id')
            ->toArray();

        if (!empty($patientIds)) {
            // Delete related records first (to avoid foreign key constraints)
            DB::table('patient_vaccine_records')->whereIn('patient_id', $patientIds)->delete();
            DB::table('patient_growth_records')->whereIn('patient_id', $patientIds)->delete();

            // Delete the patients
            DB::table('patients')->whereIn('id', $patientIds)->delete();
        }

        // Delete the parent account
        DB::table('parents')->where('id', 44)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse deletion
        // You would need to restore from backup
    }
};
