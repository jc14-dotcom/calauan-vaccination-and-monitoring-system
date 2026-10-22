<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add indexes to improve query performance for vaccination reports
     */
    public function up(): void
    {
        // Patient Vaccine Records - optimize queries for vaccination reports
        Schema::table('patient_vaccine_records', function (Blueprint $table) {
            // Check if index doesn't exist before creating
            if (!$this->indexExists('patient_vaccine_records', 'idx_pvr_vaccine')) {
                $table->index('vaccine_id', 'idx_pvr_vaccine');
            }
            if (!$this->indexExists('patient_vaccine_records', 'idx_pvr_vaccine_dose1')) {
                $table->index(['vaccine_id', 'dose_1_date'], 'idx_pvr_vaccine_dose1');
            }
            if (!$this->indexExists('patient_vaccine_records', 'idx_pvr_vaccine_dose2')) {
                $table->index(['vaccine_id', 'dose_2_date'], 'idx_pvr_vaccine_dose2');
            }
            if (!$this->indexExists('patient_vaccine_records', 'idx_pvr_vaccine_dose3')) {
                $table->index(['vaccine_id', 'dose_3_date'], 'idx_pvr_vaccine_dose3');
            }
        });
        
        // Vaccines - optimize vaccine list queries
        Schema::table('vaccines', function (Blueprint $table) {
            if (!$this->indexExists('vaccines', 'idx_vaccines_name')) {
                $table->index('vaccine_name', 'idx_vaccines_name');
            }
        });
        
        // Patients - use raw SQL for TEXT columns with key length
        if (!$this->indexExists('patients', 'idx_patients_barangay')) {
            DB::statement('ALTER TABLE patients ADD INDEX idx_patients_barangay (barangay(100))');
        }
        if (!$this->indexExists('patients', 'idx_patients_barangay_dob')) {
            DB::statement('ALTER TABLE patients ADD INDEX idx_patients_barangay_dob (barangay(100), date_of_birth(50))');
        }
    }
    
    /**
     * Check if an index exists on a table
     */
    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_vaccine_records', function (Blueprint $table) {
            $table->dropIndex('idx_pvr_vaccine');
            $table->dropIndex('idx_pvr_vaccine_dose1');
            $table->dropIndex('idx_pvr_vaccine_dose2');
            $table->dropIndex('idx_pvr_vaccine_dose3');
        });
        
        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropIndex('idx_vaccines_name');
        });
        
        // Drop patient indexes using raw SQL
        if ($this->indexExists('patients', 'idx_patients_barangay')) {
            DB::statement('ALTER TABLE patients DROP INDEX idx_patients_barangay');
        }
        if ($this->indexExists('patients', 'idx_patients_barangay_dob')) {
            DB::statement('ALTER TABLE patients DROP INDEX idx_patients_barangay_dob');
        }
    }
};
