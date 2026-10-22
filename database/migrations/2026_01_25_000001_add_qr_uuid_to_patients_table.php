<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Add QR UUID column to patients table for QR code generation.
 * 
 * This migration:
 * 1. Adds a qr_uuid column to store unique identifiers for QR codes
 * 2. Generates UUIDs for all existing patients
 * 3. Adds an index for fast lookup when scanning QR codes
 * 
 * Related Feature: QR Code System (Panel-Requested)
 * See: _archive/documentation/PANEL_FEATURES_IMPLEMENTATION_PLAN.md
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add qr_uuid column
        Schema::table('patients', function (Blueprint $table) {
            $table->char('qr_uuid', 36)->nullable()->unique()->after('id');
        });

        // Generate UUIDs for existing patients
        $patients = DB::table('patients')->whereNull('qr_uuid')->get();
        
        foreach ($patients as $patient) {
            DB::table('patients')
                ->where('id', $patient->id)
                ->update(['qr_uuid' => Str::uuid()->toString()]);
        }

        // Add index for fast lookup (the unique constraint already creates an index,
        // but we explicitly name it for clarity)
        Schema::table('patients', function (Blueprint $table) {
            $table->index('qr_uuid', 'idx_patients_qr_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_qr_uuid');
            $table->dropColumn('qr_uuid');
        });
    }
};
