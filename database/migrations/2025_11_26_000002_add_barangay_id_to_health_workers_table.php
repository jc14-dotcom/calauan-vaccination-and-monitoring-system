<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds barangay_id foreign key to health_workers table.
     * - NULL = RHU Admin (can access all barangays)
     * - Non-NULL = Barangay worker (can only access assigned barangay)
     */
    public function up(): void
    {
        Schema::table('health_workers', function (Blueprint $table) {
            // Add barangay_id column after email
            // NULL means RHU admin with full access
            $table->unsignedBigInteger('barangay_id')->nullable()->after('email');
            
            // Add foreign key constraint
            $table->foreign('barangay_id')
                  ->references('id')
                  ->on('barangays')
                  ->onDelete('set null'); // If barangay is deleted, worker becomes RHU level
            
            // Add index for performance
            $table->index('barangay_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_workers', function (Blueprint $table) {
            $table->dropForeign(['barangay_id']);
            $table->dropIndex(['barangay_id']);
            $table->dropColumn('barangay_id');
        });
    }
};
