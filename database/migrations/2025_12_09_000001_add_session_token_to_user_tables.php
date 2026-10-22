<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add session_token to user tables
 * 
 * This adds a session_token column to parents and health_workers tables
 * for single-session enforcement security. When a user logs in, a unique
 * token is generated and stored. On each request, this token is validated
 * against the session. A new login invalidates the old token, effectively
 * logging out any other sessions.
 * 
 * Security Benefits:
 * - Prevents session hijacking
 * - Enforces single active session per user
 * - Invalidates old sessions on new login
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add session_token to parents table
        Schema::table('parents', function (Blueprint $table) {
            $table->string('session_token', 64)->nullable()->after('remember_token');
            $table->timestamp('session_created_at')->nullable()->after('session_token');
        });

        // Add session_token to health_workers table
        Schema::table('health_workers', function (Blueprint $table) {
            $table->string('session_token', 64)->nullable()->after('remember_token');
            $table->timestamp('session_created_at')->nullable()->after('session_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn(['session_token', 'session_created_at']);
        });

        Schema::table('health_workers', function (Blueprint $table) {
            $table->dropColumn(['session_token', 'session_created_at']);
        });
    }
};
