<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to add separate name fields for accurate name handling.
 * 
 * This replaces the single 'name' field approach with:
 * - first_name: First name(s) including second/middle names
 * - middle_initial: M.I. (single letter, with or without period)
 * - last_name: Surname/family name
 * - suffix: Jr., Sr., III, IV, etc.
 * 
 * The original 'name' column is kept for backward compatibility
 * and will be populated by concatenating the new fields.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Add new name fields after the existing 'name' column
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('middle_initial', 5)->nullable()->after('first_name');
            $table->string('last_name', 100)->nullable()->after('middle_initial');
            $table->string('suffix', 10)->nullable()->after('last_name');
            
            // Add index for last_name for sorting purposes
            $table->index('last_name', 'idx_patients_last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_last_name');
            $table->dropColumn(['first_name', 'middle_initial', 'last_name', 'suffix']);
        });
    }
};
