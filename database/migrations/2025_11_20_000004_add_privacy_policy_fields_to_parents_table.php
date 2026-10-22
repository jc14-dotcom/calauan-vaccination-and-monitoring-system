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
        Schema::table('parents', function (Blueprint $table) {
            $table->boolean('privacy_policy_accepted')->default(false)->after('password_changed');
            $table->timestamp('privacy_policy_accepted_at')->nullable()->after('privacy_policy_accepted');
            $table->string('privacy_policy_version', 10)->default('1.0')->after('privacy_policy_accepted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn(['privacy_policy_accepted', 'privacy_policy_accepted_at', 'privacy_policy_version']);
        });
    }
};
