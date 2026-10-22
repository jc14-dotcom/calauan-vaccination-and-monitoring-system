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
        // Drop existing foreign key constraints on patient_id
        $foreignKeys = ['feedback_ibfk_1', 'fk_feedback_patient', 'feedback_patient_id_foreign'];
        foreach ($foreignKeys as $fkName) {
            try {
                Schema::table('feedback', function (Blueprint $table) use ($fkName) {
                    $table->dropForeign($fkName);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        Schema::table('feedback', function (Blueprint $table) {
            // Make patient_id nullable first (after dropping foreign key)
            $table->unsignedBigInteger('patient_id')->nullable()->change();
            
            // Add parent tracking fields only if they don't exist
            if (!Schema::hasColumn('feedback', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('parents')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('feedback', 'vaccination_schedule_id')) {
                $table->foreignId('vaccination_schedule_id')->nullable()->after('parent_id')->constrained('vaccination_schedules')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('feedback', 'barangay')) {
                $table->string('barangay')->nullable()->after('vaccination_schedule_id');
            }
            
            if (!Schema::hasColumn('feedback', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('content');
            }
        });

        // Add unique constraint to prevent duplicate submissions only if it doesn't exist
        $indexExists = collect(\DB::select("SHOW INDEX FROM feedback WHERE Key_name = 'unique_parent_schedule_feedback'"))->isNotEmpty();
        if (!$indexExists) {
            Schema::table('feedback', function (Blueprint $table) {
                $table->unique(['parent_id', 'vaccination_schedule_id'], 'unique_parent_schedule_feedback');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('unique_parent_schedule_feedback');
            
            // Drop foreign keys
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['vaccination_schedule_id']);
            
            // Drop columns
            $table->dropColumn(['parent_id', 'vaccination_schedule_id', 'barangay', 'submitted_at']);
        });
    }
};
