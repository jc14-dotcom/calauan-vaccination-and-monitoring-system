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
        Schema::create('patient_growth_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_id');
            $table->decimal('height', 5, 2)->nullable()->comment('Height in cm');
            $table->decimal('weight', 5, 2)->nullable()->comment('Weight in kg');
            $table->date('recorded_date')->comment('When measurement was taken');
            $table->unsignedInteger('recorded_by')->nullable()->comment('Health worker ID');
            $table->enum('measurement_type', ['birth', 'routine_checkup', 'vaccination_visit', 'sick_visit'])->default('routine_checkup');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();

            // Remove foreign key constraints for now to avoid compatibility issues
            // We can add indexes instead
            $table->index('patient_id');
            $table->index('recorded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_growth_records');
    }
};
