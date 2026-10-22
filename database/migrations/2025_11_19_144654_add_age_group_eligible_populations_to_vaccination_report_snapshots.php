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
            // Add three age-group specific eligible population columns
            // Keep the original column for backward compatibility
            $table->integer('eligible_population_under_1_year')->default(0)->after('eligible_population');
            $table->integer('eligible_population_0_12_months')->default(0)->after('eligible_population_under_1_year');
            $table->integer('eligible_population_13_23_months')->default(0)->after('eligible_population_0_12_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccination_report_snapshots', function (Blueprint $table) {
            $table->dropColumn([
                'eligible_population_under_1_year',
                'eligible_population_0_12_months',
                'eligible_population_13_23_months'
            ]);
        });
    }
};
