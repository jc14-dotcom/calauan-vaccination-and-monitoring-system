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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_phone');
            $table->text('message');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->string('gateway_response')->nullable();
            $table->string('gateway_message_id')->nullable();
            $table->morphs('notifiable');
            $table->uuid('notification_id')->nullable();
            $table->decimal('cost', 8, 2)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
