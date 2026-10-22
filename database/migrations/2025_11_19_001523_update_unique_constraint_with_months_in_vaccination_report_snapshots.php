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
            // Drop the old unique constraint (without months)
            $table->dropUnique('unique_report_cell_with_version');
            
            // Add new unique constraint that includes month_start and month_end
            // This allows same year/quarter/barangay/vaccine/version but different month ranges
            $table->unique(
                ['year', 'quarter_start', 'quarter_end', 'month_start', 'month_end', 'barangay', 'vaccine_name', 'version'],
                'unique_report_cell_with_version_and_months'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccination_report_snapshots', function (Blueprint $table) {
            // Drop the new constraint
            $table->dropUnique('unique_report_cell_with_version_and_months');
            
            // Restore the old constraint (without months)
            $table->unique(
                ['year', 'quarter_start', 'quarter_end', 'barangay', 'vaccine_name', 'version'],
                'unique_report_cell_with_version'
            );
        });
    }
};
