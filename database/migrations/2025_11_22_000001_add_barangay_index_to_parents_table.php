<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add index to parents.barangay for fast notification queries.
     * This enables efficient filtering when sending vaccination schedule notifications.
     */
    public function up(): void
    {
        // Check if index doesn't already exist
        $indexExists = DB::select(
            "SHOW INDEX FROM parents WHERE Key_name = ?", 
            ['idx_parents_barangay']
        );
        
        if (empty($indexExists)) {
            Schema::table('parents', function (Blueprint $table) {
                $table->index('barangay', 'idx_parents_barangay');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropIndex('idx_parents_barangay');
        });
    }
};
