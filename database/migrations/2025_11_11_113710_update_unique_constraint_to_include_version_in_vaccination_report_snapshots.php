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
            // Drop the old unique constraint
            $table->dropUnique('unique_report_cell');
            
            // Add new unique constraint that includes version
            $table->unique(['year', 'quarter_start', 'quarter_end', 'barangay', 'vaccine_name', 'version'], 'unique_report_cell_with_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccination_report_snapshots', function (Blueprint $table) {
            // Drop the new constraint
            $table->dropUnique('unique_report_cell_with_version');
            
            // Restore the old constraint
            $table->unique(['year', 'quarter_start', 'quarter_end', 'barangay', 'vaccine_name'], 'unique_report_cell');
        });
    }
};
