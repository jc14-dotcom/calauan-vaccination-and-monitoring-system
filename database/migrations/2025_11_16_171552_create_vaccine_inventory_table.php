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
        Schema::create('vaccine_inventory', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to vaccines table
            $table->integer('vaccine_id');
            $table->foreign('vaccine_id')
                  ->references('id')
                  ->on('vaccines')
                  ->onDelete('cascade');
            
            // Inventory tracking (all integers, no decimals)
            $table->integer('doses_per_bottle')->comment('Number of doses in each bottle');
            $table->integer('bottles_total')->default(0)->comment('Total bottles received in this batch');
            $table->integer('bottles_used')->default(0)->comment('Bottles consumed (calculated)');
            $table->integer('doses_used')->default(0)->comment('Individual doses consumed');
            
            // Batch information
            $table->date('received_date')->comment('Date when stock was received');
            
            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable()->comment('Health worker who added stock');
            $table->foreign('created_by')
                  ->references('id')
                  ->on('health_workers')
                  ->onDelete('set null');
            
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Last updated by');
            $table->foreign('updated_by')
                  ->references('id')
                  ->on('health_workers')
                  ->onDelete('set null');
            
            // Additional notes
            $table->text('notes')->nullable()->comment('Additional information about this stock batch');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('vaccine_id');
            $table->index('received_date');
            $table->index(['vaccine_id', 'received_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccine_inventory');
    }
};
