<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->boolean('is_closed')->default(false)->after('worker_id');
            $table->timestamp('closed_at')->nullable()->after('is_closed');
            $table->timestamp('reopened_at')->nullable()->after('closed_at');
            $table->foreignId('reopened_by')->nullable()->after('reopened_at')
                ->constrained('users')->nullOnDelete();
        });

        DB::table('conversations')
            ->whereIn('job_id', DB::table('service_jobs')
                ->select('id')
                ->whereIn('status', ['completed', 'rated']))
            ->update([
                'is_closed' => true,
                'closed_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reopened_by');
            $table->dropColumn(['is_closed', 'closed_at', 'reopened_at']);
        });
    }
};
