<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add indexes to frequently queried columns for performance optimization.
     * These indexes dramatically speed up searches and filters.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Check and add indexes that don't already exist
            
            // Index for contact number search (NEW)
            $table->index('contact_no', 'idx_patients_contact_no');
            
            // Index for date of birth (NEW)
            $table->index('date_of_birth', 'idx_patients_dob');
            
            // Index for created_at for ORDER BY (NEW)
            $table->index('created_at', 'idx_patients_created_at');
            
            // Note: These already exist from previous migrations:
            // - idx_patients_name (name column)
            // - idx_patients_barangay (barangay column)
            // - idx_patients_barangay_dob (composite: barangay + date_of_birth)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Drop only the indexes we created
            $table->dropIndex('idx_patients_created_at');
            $table->dropIndex('idx_patients_dob');
            $table->dropIndex('idx_patients_contact_no');
        });
    }
};
