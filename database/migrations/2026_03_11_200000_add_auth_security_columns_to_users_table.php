<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('verification_channel')->nullable()->after('phone_verified_at');
            $table->string('verification_token')->nullable()->after('verification_channel');
            $table->timestamp('verification_token_expires_at')->nullable()->after('verification_token');
            $table->string('password_reset_channel')->nullable()->after('verification_token_expires_at');
            $table->string('password_reset_token')->nullable()->after('password_reset_channel');
            $table->timestamp('password_reset_token_expires_at')->nullable()->after('password_reset_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_verified_at',
                'verification_channel',
                'verification_token',
                'verification_token_expires_at',
                'password_reset_channel',
                'password_reset_token',
                'password_reset_token_expires_at',
            ]);
        });
    }
};
