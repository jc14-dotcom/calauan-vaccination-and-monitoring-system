<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vaccines', function (Blueprint $table) {
            $table->unsignedInteger('total_doses')->default(1)->after('doses_description');
            $table->string('target_age_group', 50)->default('under_1_year')->after('total_doses');
            $table->string('acronym', 50)->nullable()->after('target_age_group');
            $table->boolean('is_standard')->default(false)->after('acronym');
            $table->boolean('is_active_in_reports')->default(false)->after('is_standard');
            $table->unsignedInteger('sort_order')->default(99)->after('is_active_in_reports');
        });
    }

    public function down(): void
    {
        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropColumn([
                'total_doses',
                'target_age_group',
                'acronym',
                'is_standard',
                'is_active_in_reports',
                'sort_order',
            ]);
        });
    }
};
