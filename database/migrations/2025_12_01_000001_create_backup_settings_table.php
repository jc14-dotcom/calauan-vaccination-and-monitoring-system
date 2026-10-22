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
        Schema::create('backup_settings', function (Blueprint $table) {
            $table->id();
            $table->string('schedule')->default('weekly'); // daily, weekly, quarterly, yearly
            $table->boolean('enabled')->default(true); // Always enabled by default
            $table->timestamp('last_backup_at')->nullable();
            $table->timestamp('next_backup_at')->nullable();
            $table->integer('retention_count')->default(8); // Number of backups to keep
            $table->timestamps();
        });

        // Insert default settings
        DB::table('backup_settings')->insert([
            'schedule' => 'weekly',
            'enabled' => true,
            'retention_count' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_settings');
    }
};
