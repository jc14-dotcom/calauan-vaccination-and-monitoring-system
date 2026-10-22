<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add account management fields to health_workers table.
 * - is_active: for soft deactivation
 * - created_by: tracks who created the account
 * - last_login_at: tracks last login time
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_workers', function (Blueprint $table) {
            if (!Schema::hasColumn('health_workers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('barangay_id');
            }
            if (!Schema::hasColumn('health_workers', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
                $table->foreign('created_by')->references('id')->on('health_workers')->onDelete('set null');
            }
            if (!Schema::hasColumn('health_workers', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_workers', function (Blueprint $table) {
            if (Schema::hasColumn('health_workers', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
            if (Schema::hasColumn('health_workers', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('health_workers', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
