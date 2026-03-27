<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_negotiations', function (Blueprint $table) {
            $table->json('history')->nullable()->after('message');
        });

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->decimal('agreed_amount', 10, 2)->nullable()->after('budget');
        });

        DB::table('service_jobs')
            ->whereNotNull('assigned_to')
            ->whereNull('agreed_amount')
            ->update([
                'agreed_amount' => DB::raw('budget'),
            ]);
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropColumn('agreed_amount');
        });

        Schema::table('job_negotiations', function (Blueprint $table) {
            $table->dropColumn('history');
        });
    }
};
