<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->string('slug')->nullable();
        });

        DB::table('service_jobs')
            ->select(['id', 'title'])
            ->orderBy('id')
            ->chunkById(200, function ($jobs): void {
                foreach ($jobs as $job) {
                    $base = Str::slug($job->title) ?: 'job';

                    do {
                        $slug = $base . '-' . Str::lower(Str::random(6));
                        $exists = DB::table('service_jobs')->where('slug', $slug)->exists();
                    } while ($exists);

                    DB::table('service_jobs')
                        ->where('id', $job->id)
                        ->update(['slug' => $slug]);
                }
            });

        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
