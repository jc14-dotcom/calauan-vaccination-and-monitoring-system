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
        Schema::create('vaccination_report_snapshots', function (Blueprint $table) {
            $table->id();
            
            // Report Metadata
            $table->integer('year');
            $table->tinyInteger('quarter_start'); // 1-4
            $table->tinyInteger('quarter_end');   // 1-4
            $table->string('barangay', 100)->nullable(); // NULL = totals row
            
            // Vaccine & Demographic
            $table->string('vaccine_name', 100);
            
            // Data Values
            $table->integer('male_count')->default(0);
            $table->integer('female_count')->default(0);
            $table->integer('total_count')->default(0);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->integer('eligible_population')->default(0);
            
            // Tracking
            $table->enum('data_source', ['calculated', 'manual_edit', 'imported'])->default('calculated');
            $table->boolean('is_locked')->default(false);
            
            // Audit Trail
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->unique(['year', 'quarter_start', 'quarter_end', 'barangay', 'vaccine_name'], 'unique_report_cell');
            $table->index(['year', 'quarter_start', 'quarter_end'], 'idx_year_quarter');
            $table->index('barangay', 'idx_barangay');
            
            // Foreign keys - reference health_workers table
            $table->foreign('created_by')->references('id')->on('health_workers')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('health_workers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccination_report_snapshots');
    }
};
