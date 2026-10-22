<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds index to qr_uuid for faster QR code lookups.
     */
    public function up(): void
    {
        // Check if index already exists
        $indexExists = collect(DB::select("SHOW INDEX FROM patients WHERE Key_name = 'patients_qr_uuid_unique'"))->isNotEmpty();
        
        if (!$indexExists) {
            Schema::table('patients', function (Blueprint $table) {
                $table->unique('qr_uuid', 'patients_qr_uuid_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexExists = collect(DB::select("SHOW INDEX FROM patients WHERE Key_name = 'patients_qr_uuid_unique'"))->isNotEmpty();
        
        if ($indexExists) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropUnique('patients_qr_uuid_unique');
            });
        }
    }
};
