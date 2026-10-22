<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a unique constraint to prevent duplicate vaccine assignments
        Schema::table('patient_vaccine_records', function (Blueprint $table) {
            $table->unique(['patient_id', 'vaccine_id'], 'unique_patient_vaccine');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_vaccine_records', function (Blueprint $table) {
            $table->dropUnique('unique_patient_vaccine');
        });
    }
};
