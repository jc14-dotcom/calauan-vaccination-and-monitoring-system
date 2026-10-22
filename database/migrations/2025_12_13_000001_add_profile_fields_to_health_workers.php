<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add profile fields to health_workers table.
 * This allows health workers to have display names and contact numbers
 * for better identification in activity logs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_workers', function (Blueprint $table) {
            if (!Schema::hasColumn('health_workers', 'name')) {
                $table->string('name')->nullable()->after('username');
            }
            if (!Schema::hasColumn('health_workers', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_workers', function (Blueprint $table) {
            if (Schema::hasColumn('health_workers', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('health_workers', 'contact_number')) {
                $table->dropColumn('contact_number');
            }
        });
    }
};
