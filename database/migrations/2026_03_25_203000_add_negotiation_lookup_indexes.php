<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_negotiations', function (Blueprint $table) {
            $table->index(['job_id', 'worker_id', 'id'], 'job_negotiations_job_worker_id_idx');
            $table->index(['job_id', 'status', 'id'], 'job_negotiations_job_status_id_idx');
            $table->index(['worker_id', 'status'], 'job_negotiations_worker_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('job_negotiations', function (Blueprint $table) {
            $table->dropIndex('job_negotiations_job_worker_id_idx');
            $table->dropIndex('job_negotiations_job_status_id_idx');
            $table->dropIndex('job_negotiations_worker_status_idx');
        });
    }
};
