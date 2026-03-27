<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('idempotency_key')->nullable()->after('reference');
            $table->timestamp('verified_at')->nullable()->after('status');
            $table->json('provider_payload')->nullable()->after('verified_at');

            $table->unique('reference');
            $table->unique('idempotency_key');
            $table->index(['job_id', 'user_id', 'status'], 'payments_job_user_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_job_user_status_index');
            $table->dropUnique(['reference']);
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn([
                'idempotency_key',
                'verified_at',
                'provider_payload',
            ]);
        });
    }
};
