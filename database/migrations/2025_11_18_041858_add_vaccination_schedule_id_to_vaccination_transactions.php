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
        Schema::table('vaccination_transactions', function (Blueprint $table) {
            // Add foreign key to link vaccination to schedule
            $table->foreignId('vaccination_schedule_id')
                ->nullable()
                ->after('vaccination_session_id')
                ->constrained('vaccination_schedules')
                ->onDelete('set null');
                
            // Add index for faster queries
            $table->index('vaccination_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccination_transactions', function (Blueprint $table) {
            $table->dropForeign(['vaccination_schedule_id']);
            $table->dropIndex(['vaccination_schedule_id']);
            $table->dropColumn('vaccination_schedule_id');
        });
    }
};
