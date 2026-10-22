<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccine_dose_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('vaccine_id');
            $table->foreign('vaccine_id')
                  ->references('id')
                  ->on('vaccines')
                  ->cascadeOnDelete();
            $table->unsignedTinyInteger('dose_number');
            $table->decimal('min_age_months', 8, 2)->default(0);
            $table->decimal('max_age_months', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['vaccine_id', 'dose_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccine_dose_configs');
    }
};
