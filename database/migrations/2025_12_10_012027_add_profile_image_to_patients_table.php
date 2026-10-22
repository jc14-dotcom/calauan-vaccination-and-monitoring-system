<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds profile_image column to store child photo filename
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Store only the filename (e.g., "123.jpg")
            // Actual file stored in storage/app/public/children/
            $table->string('profile_image', 100)->nullable()->after('sex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('profile_image');
        });
    }
};
