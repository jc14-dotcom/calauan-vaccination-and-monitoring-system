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
        Schema::table('vaccination_report_snapshots', function (Blueprint $table) {
            $table->tinyInteger('month_start')->nullable()->after('quarter_start'); // 1-12
            $table->tinyInteger('month_end')->nullable()->after('quarter_end');   // 1-12
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccination_report_snapshots', function (Blueprint $table) {
            $table->dropColumn(['month_start', 'month_end']);
        });
    }
};
