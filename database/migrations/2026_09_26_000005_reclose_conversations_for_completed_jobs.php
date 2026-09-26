<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('conversations')
            ->whereIn('job_id', DB::table('service_jobs')
                ->select('id')
                ->whereIn('status', ['completed', 'rated']))
            ->where('is_closed', false)
            ->update([
                'is_closed' => true,
                'closed_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Closure state is intentional history; rolling this data repair back
        // would incorrectly reopen completed conversations.
    }
};
