<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * These indexes optimize the activity log queries for:
     * - Filtering by log_name, event, causer_type
     * - Searching by specific causer (causer_id + causer_type)
     * - Date range queries (created_at)
     * - Sorting by created_at (most common)
     */
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // Get existing indexes
            $existingIndexes = collect(DB::select("SHOW INDEX FROM activity_log"))
                ->pluck('Key_name')
                ->unique()
                ->toArray();
            
            // Index for filtering by log name (model type)
            if (!in_array('activity_log_log_name_index', $existingIndexes)) {
                $table->index('log_name', 'activity_log_log_name_index');
            }
            
            // Index for filtering by event type (created, updated, deleted, etc.)
            if (!in_array('activity_log_event_index', $existingIndexes)) {
                $table->index('event', 'activity_log_event_index');
            }
            
            // Composite index for causer lookups (who performed the action)
            if (!in_array('activity_log_causer_index', $existingIndexes)) {
                $table->index(['causer_type', 'causer_id'], 'activity_log_causer_index');
            }
            
            // Index for date range filtering and sorting (most common operation)
            if (!in_array('activity_log_created_at_index', $existingIndexes)) {
                $table->index('created_at', 'activity_log_created_at_index');
            }
            
            // Composite index for subject lookups (what was affected)
            if (!in_array('activity_log_subject_index', $existingIndexes)) {
                $table->index(['subject_type', 'subject_id'], 'activity_log_subject_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM activity_log"))
                ->pluck('Key_name')
                ->unique()
                ->toArray();
            
            if (in_array('activity_log_log_name_index', $existingIndexes)) {
                $table->dropIndex('activity_log_log_name_index');
            }
            if (in_array('activity_log_event_index', $existingIndexes)) {
                $table->dropIndex('activity_log_event_index');
            }
            if (in_array('activity_log_causer_index', $existingIndexes)) {
                $table->dropIndex('activity_log_causer_index');
            }
            if (in_array('activity_log_created_at_index', $existingIndexes)) {
                $table->dropIndex('activity_log_created_at_index');
            }
            if (in_array('activity_log_subject_index', $existingIndexes)) {
                $table->dropIndex('activity_log_subject_index');
            }
        });
    }
};
