<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->unique('job_id');
            $table->index('client_id');
            $table->index('worker_id');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique(['job_id']);
            $table->dropIndex(['client_id']);
            $table->dropIndex(['worker_id']);
        });
    }
};
