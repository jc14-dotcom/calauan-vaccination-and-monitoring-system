<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix Production Schema Differences
 * 
 * This migration:
 * 1. Removes incorrect 'email' column from patients table (if exists)
 * 2. Removes incorrect 'email' column from patient_vaccine_records table (if exists)
 * 3. Removes incorrect 'email' column from vaccines table (if exists)
 * 4. Removes 'birthday' column from parents table (production has it, local doesn't)
 * 5. Renames 'email' to 'submitted_at' in feedback table OR adds submitted_at if missing
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Remove email column from patients table (shouldn't exist)
        if (Schema::hasColumn('patients', 'email')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        // 2. Remove email column from patient_vaccine_records table (shouldn't exist)
        if (Schema::hasColumn('patient_vaccine_records', 'email')) {
            Schema::table('patient_vaccine_records', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        // 3. Remove email column from vaccines table (shouldn't exist)
        if (Schema::hasColumn('vaccines', 'email')) {
            Schema::table('vaccines', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        // 4. Remove birthday column from parents table (production has it, local doesn't)
        if (Schema::hasColumn('parents', 'birthday')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->dropColumn('birthday');
            });
        }

        // 5. Fix feedback table - production has 'email' but should have 'submitted_at'
        Schema::table('feedback', function (Blueprint $table) {
            // Remove email column if it exists (production has this incorrectly)
            if (Schema::hasColumn('feedback', 'email')) {
                $table->dropColumn('email');
            }
        });

        // Add submitted_at if it doesn't exist
        if (!Schema::hasColumn('feedback', 'submitted_at')) {
            Schema::table('feedback', function (Blueprint $table) {
                $table->timestamp('submitted_at')->nullable()->after('content');
            });
        }
    }

    public function down(): void
    {
        // This is a cleanup migration - down() would restore incorrect state
        // so we leave it mostly empty
        
        if (!Schema::hasColumn('feedback', 'submitted_at')) {
            Schema::table('feedback', function (Blueprint $table) {
                $table->timestamp('submitted_at')->nullable()->after('content');
            });
        }
    }
};
