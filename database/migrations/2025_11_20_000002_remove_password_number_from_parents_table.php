<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * IMPORTANT: Only run this migration after testing the new password system!
     * This migration removes the password_number column which is no longer used
     * since we switched to random password generation (RHUKC-XXXXX with random chars).
     */
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn('password_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->integer('password_number')->nullable()->after('password');
        });
    }
};
