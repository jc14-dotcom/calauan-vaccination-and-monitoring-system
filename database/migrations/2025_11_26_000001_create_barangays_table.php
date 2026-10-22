<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the barangays lookup table for the multi-tenant barangay system.
     * This table serves as the single source of truth for all barangay names.
     */
    public function up(): void
    {
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();           // Barangay name (e.g., "Dayap", "Balayhangin")
            $table->string('code', 20)->nullable();     // Optional short code
            $table->boolean('is_active')->default(true); // For soft-disable without deletion
            $table->boolean('has_scheduled_vaccination')->default(true); // Whether vaccination schedules can be created for this barangay
            $table->text('notes')->nullable();          // Optional notes (e.g., "RHU located here")
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangays');
    }
};
