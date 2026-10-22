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
        Schema::table('vaccination_report_snapshots', function (Blueprint $table) {
            // Version tracking
            $table->integer('version')->default(1)->after('vaccine_name');
            $table->timestamp('saved_at')->nullable()->after('updated_at');
            $table->unsignedBigInteger('saved_by')->nullable()->after('saved_at');
            
            // Soft delete columns
            $table->softDeletes(); // Adds deleted_at column
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
            $table->text('deletion_reason')->nullable()->after('deleted_by');
            
            // Foreign key for saved_by
            $table->foreign('saved_by')->references('id')->on('health_workers')->onDelete('set null');
            
            // Foreign key for deleted_by
            $table->foreign('deleted_by')->references('id')->on('health_workers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccination_report_snapshots', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['saved_by']);
            $table->dropForeign(['deleted_by']);
            
            // Drop columns
            $table->dropColumn([
                'version',
                'saved_at',
                'saved_by',
                'deleted_at',
                'deleted_by',
                'deletion_reason'
            ]);
        });
    }
};
