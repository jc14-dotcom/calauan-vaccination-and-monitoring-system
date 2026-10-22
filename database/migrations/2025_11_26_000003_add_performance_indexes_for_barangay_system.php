<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Performance Optimization Migration for Barangay System
 * 
 * This migration adds indexes to improve query performance for:
 * - Filtering patients by barangay (main use case for barangay workers)
 * - Filtering feedback by barangay
 * - Filtering vaccination schedules by barangay
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check and add index to vaccination_schedules.barangay if not exists
        if (!$this->indexExists('vaccination_schedules', 'idx_vacc_schedules_barangay')) {
            Schema::table('vaccination_schedules', function (Blueprint $table) {
                $table->index('barangay', 'idx_vacc_schedules_barangay');
            });
        }
        
        // Add composite index for vaccination_schedules (date + status + barangay) for efficient filtering
        if (!$this->indexExists('vaccination_schedules', 'idx_vacc_schedules_date_status')) {
            Schema::table('vaccination_schedules', function (Blueprint $table) {
                $table->index(['vaccination_date', 'status'], 'idx_vacc_schedules_date_status');
            });
        }
        
        // Check and add index to feedback.barangay if not exists
        if (Schema::hasColumn('feedback', 'barangay') && !$this->indexExists('feedback', 'idx_feedback_barangay')) {
            Schema::table('feedback', function (Blueprint $table) {
                $table->index('barangay', 'idx_feedback_barangay');
            });
        }
        
        // Add composite index on patient_vaccine_records for efficient vaccination queries
        if (!$this->indexExists('patient_vaccine_records', 'idx_pvr_patient_dose1')) {
            Schema::table('patient_vaccine_records', function (Blueprint $table) {
                $table->index(['patient_id', 'dose_1_date'], 'idx_pvr_patient_dose1');
            });
        }
        
        // Add index on barangays.name for efficient lookups
        if (!$this->indexExists('barangays', 'idx_barangays_name')) {
            Schema::table('barangays', function (Blueprint $table) {
                $table->index('name', 'idx_barangays_name');
            });
        }
        
        // Add composite index on barangays for schedulable queries
        if (!$this->indexExists('barangays', 'idx_barangays_active_schedulable')) {
            Schema::table('barangays', function (Blueprint $table) {
                $table->index(['is_active', 'has_scheduled_vaccination'], 'idx_barangays_active_schedulable');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes safely
        Schema::table('vaccination_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_vacc_schedules_barangay');
        });
        
        Schema::table('vaccination_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_vacc_schedules_date_status');
        });
        
        if (Schema::hasColumn('feedback', 'barangay')) {
            Schema::table('feedback', function (Blueprint $table) {
                $table->dropIndex('idx_feedback_barangay');
            });
        }
        
        Schema::table('patient_vaccine_records', function (Blueprint $table) {
            $table->dropIndex('idx_pvr_patient_dose1');
        });
        
        Schema::table('barangays', function (Blueprint $table) {
            $table->dropIndex('idx_barangays_name');
        });
        
        Schema::table('barangays', function (Blueprint $table) {
            $table->dropIndex('idx_barangays_active_schedulable');
        });
    }
    
    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
