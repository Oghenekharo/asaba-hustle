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
        Schema::table('job_negotiations', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable();
        });

        DB::table('job_negotiations')->select('id')->orderBy('id')->chunkById(200, function ($negotiations): void {
            foreach ($negotiations as $negotiation) {
                DB::table('job_negotiations')->where('id', $negotiation->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            }
        });

        Schema::table('job_negotiations', function (Blueprint $table): void {
            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('job_negotiations', function (Blueprint $table): void {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
