<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds remember_token column for "Remember Me" functionality
     */
    public function up(): void
    {
        // Add remember_token to parents table
        if (!Schema::hasColumn('parents', 'remember_token')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->rememberToken()->after('password');
            });
        }

        // Add remember_token to health_workers table
        if (!Schema::hasColumn('health_workers', 'remember_token')) {
            Schema::table('health_workers', function (Blueprint $table) {
                $table->rememberToken()->after('password');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('parents', 'remember_token')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }

        if (Schema::hasColumn('health_workers', 'remember_token')) {
            Schema::table('health_workers', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }
};
