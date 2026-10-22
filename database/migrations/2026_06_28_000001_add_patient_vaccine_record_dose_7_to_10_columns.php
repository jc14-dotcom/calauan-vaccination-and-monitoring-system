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
        if (!Schema::hasTable('patient_vaccine_records')) {
            return;
        }

        Schema::table('patient_vaccine_records', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_vaccine_records', 'dose_4_date')) {
                $table->date('dose_4_date')->nullable()->after('dose_3_date');
            }
            if (!Schema::hasColumn('patient_vaccine_records', 'dose_5_date')) {
                $table->date('dose_5_date')->nullable()->after('dose_4_date');
            }
            if (!Schema::hasColumn('patient_vaccine_records', 'dose_6_date')) {
                $table->date('dose_6_date')->nullable()->after('dose_5_date');
            }
            if (!Schema::hasColumn('patient_vaccine_records', 'dose_7_date')) {
                $table->date('dose_7_date')->nullable()->after('dose_6_date');
            }
            if (!Schema::hasColumn('patient_vaccine_records', 'dose_8_date')) {
                $table->date('dose_8_date')->nullable()->after('dose_7_date');
            }
            if (!Schema::hasColumn('patient_vaccine_records', 'dose_9_date')) {
                $table->date('dose_9_date')->nullable()->after('dose_8_date');
            }
            if (!Schema::hasColumn('patient_vaccine_records', 'dose_10_date')) {
                $table->date('dose_10_date')->nullable()->after('dose_9_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('patient_vaccine_records')) {
            return;
        }

        Schema::table('patient_vaccine_records', function (Blueprint $table) {
            if (Schema::hasColumn('patient_vaccine_records', 'dose_10_date')) {
                $table->dropColumn('dose_10_date');
            }
            if (Schema::hasColumn('patient_vaccine_records', 'dose_9_date')) {
                $table->dropColumn('dose_9_date');
            }
            if (Schema::hasColumn('patient_vaccine_records', 'dose_8_date')) {
                $table->dropColumn('dose_8_date');
            }
            if (Schema::hasColumn('patient_vaccine_records', 'dose_7_date')) {
                $table->dropColumn('dose_7_date');
            }
        });
    }
};
