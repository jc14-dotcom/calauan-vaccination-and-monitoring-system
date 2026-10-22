<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add indexes for vaccination status search and filtering
     */
    public function up(): void
    {
        // Add indexes for name and contact_no search (VARCHAR columns - no length needed)
        Schema::table('patients', function (Blueprint $table) {
            if (!$this->indexExists('patients', 'idx_patients_name')) {
                $table->index('name', 'idx_patients_name');
            }
            
            if (!$this->indexExists('patients', 'idx_patients_contact')) {
                $table->index('contact_no', 'idx_patients_contact');
            }
            
            if (!$this->indexExists('patients', 'idx_patients_created')) {
                $table->index('created_at', 'idx_patients_created');
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if ($this->indexExists('patients', 'idx_patients_name')) {
                $table->dropIndex('idx_patients_name');
            }
            
            if ($this->indexExists('patients', 'idx_patients_contact')) {
                $table->dropIndex('idx_patients_contact');
            }
            
            if ($this->indexExists('patients', 'idx_patients_created')) {
                $table->dropIndex('idx_patients_created');
            }
        });
    }
};
