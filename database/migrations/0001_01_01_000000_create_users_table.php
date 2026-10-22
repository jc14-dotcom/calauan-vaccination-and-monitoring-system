<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table for health worker admin
        Schema::create('health_workers', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique(); // Username for login
            $table->string('password'); // Password for login
            $table->string('email')->unique(); // Email for the health worker admin
            $table->string('password_reset_token')->nullable(); // Token for password reset
            $table->timestamp('password_reset_requested_at')->nullable(); // Timestamp for password reset requests
            $table->timestamps(); // Adds created_at and updated_at
        });

        // Table for parent users
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique(); // Username for login
            $table->date('birthday'); // Birthday for login
            $table->string('password'); // Password for login
            $table->timestamps(); // Adds created_at and updated_at for tracking account creation and updates
        });

        // Table for sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // Session ID
            $table->foreignId('user_id')->nullable()->index(); // Optional: Link to a user if needed
            $table->string('ip_address', 45)->nullable(); // IP address of the user
            $table->text('user_agent')->nullable(); // User agent string
            $table->longText('payload'); // Session payload
            $table->integer('last_activity')->index(); // Last activity timestamp
            $table->timestamps(); // Optional: If you want timestamps
        });

        // Insert pre-defined health worker admin record
        DB::table('health_workers')->insert([
            'username' => 'admin_healthworker', // Replace with actual username
            'password' => bcrypt('secure_password'), // Replace with actual password
            'email' => 'admin@example.com' // Replace with actual email
        ]);

      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions'); // Drop sessions table first
        Schema::dropIfExists('health_workers');
        Schema::dropIfExists('parents');
    }
};
