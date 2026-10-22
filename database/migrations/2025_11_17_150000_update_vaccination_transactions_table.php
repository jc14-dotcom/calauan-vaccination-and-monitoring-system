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
            // Drop foreign key constraint first
            $table->dropForeign(['inventory_id']);
            
            // Drop the inventory_id column
            $table->dropColumn('inventory_id');
            
            // Add vaccination_session_id column
            $table->string('vaccination_session_id')->nullable()->after('id');
            
            // Add indexes for performance
            $table->index('vaccination_session_id');
            $table->index('created_at'); // For "vaccinated today" queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccination_transactions', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['vaccination_session_id']);
            $table->dropIndex(['created_at']);
            
            // Remove vaccination_session_id column
            $table->dropColumn('vaccination_session_id');
            
            // Re-add inventory_id column
            $table->unsignedBigInteger('inventory_id')->after('id');
            $table->foreign('inventory_id')
                  ->references('id')
                  ->on('vaccine_inventory')
                  ->onDelete('restrict');
        });
    }
};
