<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * HISTORICAL NOTE (2025-11-15):
     * This migration was created to support field-level encryption.
     * As of November 2025, selective encryption is now used:
     * - Searchable fields (name, barangay, contact_no, date_of_birth) are NO LONGER encrypted
     * - Sensitive fields (mother_name, father_name, address, etc.) remain encrypted
     * 
     * See migration: 2025_11_15_153042_decrypt_patient_searchable_fields.php
     */
    public function up(): void
    {
        // Patients table - Changed to TEXT to support encryption
        // NOTE: Some fields have been reverted to VARCHAR by later migrations
        Schema::table('patients', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('date_of_birth')->change();
            $table->text('place_of_birth')->change();
            $table->text('birth_height')->change();
            $table->text('birth_weight')->change();
            $table->text('barangay')->change();
            $table->text('address')->change();
            $table->text('mother_name')->change();
            $table->text('father_name')->change();
            $table->text('contact_no')->change();
            // $table->text('sex')->change(); // Uncomment if you encrypt 'sex'
        });

        // Parents table
        // Schema::table('parents', function (Blueprint $table) {
        //     // $table->text('birthday')->change();
        //     // $table->text('password_number')->change();
        //     $table->text('email')->change();
        // });

        // Users table
        // Schema::table('users', function (Blueprint $table) {
        //     $table->text('name')->change();
        //     $table->text('email')->change();
        //     $table->text('birthdate')->change();
        // });

        // // Feedback table (if content is encrypted)
        // Schema::table('feedback', function (Blueprint $table) {
        //     $table->text('content')->change();
        // });

        // // PatientVaccineRecord table (if remarks is encrypted)
        // Schema::table('patient_vaccine_records', function (Blueprint $table) {
        //     $table->text('remarks')->change();
        // });

        // // HealthWorkers table (if email is encrypted)
        // Schema::table('health_workers', function (Blueprint $table) {
        //     $table->text('email')->change();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, revert columns to previous types if needed
    }
};
