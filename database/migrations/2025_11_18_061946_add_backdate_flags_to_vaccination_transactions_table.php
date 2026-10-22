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
            $table->boolean('administered_elsewhere')->default(false)->after('vaccination_schedule_id');
            $table->boolean('stock_override')->default(false)->after('administered_elsewhere');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccination_transactions', function (Blueprint $table) {
            $table->dropColumn(['administered_elsewhere', 'stock_override']);
        });
    }
};
