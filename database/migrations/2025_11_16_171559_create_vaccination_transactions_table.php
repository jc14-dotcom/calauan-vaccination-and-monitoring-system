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
        Schema::create('vaccination_transactions', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to vaccine_inventory (which batch was used)
            $table->unsignedBigInteger('inventory_id');
            $table->foreign('inventory_id')
                  ->references('id')
                  ->on('vaccine_inventory')
                  ->onDelete('restrict'); // Don't allow deletion if transactions exist
            
            // Foreign key to patient
            $table->integer('patient_id');
            $table->foreign('patient_id')
                  ->references('id')
                  ->on('patients')
                  ->onDelete('cascade');
            
            // Foreign key to vaccine
            $table->integer('vaccine_id');
            $table->foreign('vaccine_id')
                  ->references('id')
                  ->on('vaccines')
                  ->onDelete('cascade');
            
            // Foreign key to patient_vaccine_record
            $table->integer('patient_vaccine_record_id');
            $table->foreign('patient_vaccine_record_id')
                  ->references('id')
                  ->on('patient_vaccine_records')
                  ->onDelete('cascade');
            
            // Vaccination details
            $table->integer('dose_number')->comment('Which dose: 1, 2, or 3');
            $table->integer('doses_deducted')->default(1)->comment('Number of doses deducted (usually 1)');
            $table->date('vaccinated_at')->comment('Date of vaccination');
            
            // Audit fields
            $table->unsignedBigInteger('vaccinated_by')->nullable()->comment('Health worker who administered vaccine');
            $table->foreign('vaccinated_by')
                  ->references('id')
                  ->on('health_workers')
                  ->onDelete('set null');
            
            // Additional notes
            $table->text('notes')->nullable()->comment('Additional information about this vaccination');
            
            $table->timestamps();
            
            // Indexes for performance and audit queries
            $table->index('inventory_id');
            $table->index('patient_id');
            $table->index('vaccine_id');
            $table->index('vaccinated_at');
            $table->index(['patient_id', 'vaccine_id', 'dose_number']); // Composite for uniqueness check
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccination_transactions');
    }
};
