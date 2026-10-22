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
        // Get the parent IDs for patients 52 and 53
        $parentIds = DB::table('patients')
            ->whereIn('id', [52, 53])
            ->pluck('parent_id')
            ->unique()
            ->toArray();

        // Delete related records first (to avoid foreign key constraints)
        DB::table('patient_vaccine_records')->whereIn('patient_id', [52, 53])->delete();
        DB::table('patient_growth_records')->whereIn('patient_id', [52, 53])->delete();

        // Delete the patients
        DB::table('patients')->whereIn('id', [52, 53])->delete();

        // Delete the parent accounts (only if they have no other children)
        foreach ($parentIds as $parentId) {
            $hasOtherChildren = DB::table('patients')
                ->where('parent_id', $parentId)
                ->exists();

            if (!$hasOtherChildren) {
                DB::table('parents')->where('id', $parentId)->delete();
            }
        }
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
