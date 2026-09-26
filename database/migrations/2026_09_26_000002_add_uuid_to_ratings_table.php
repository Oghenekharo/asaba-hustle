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
        Schema::table('ratings', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable();
        });

        DB::table('ratings')->select('id')->orderBy('id')->chunkById(200, function ($ratings): void {
            foreach ($ratings as $rating) {
                DB::table('ratings')->where('id', $rating->id)->update(['uuid' => (string) Str::uuid()]);
            }
        });

        Schema::table('ratings', function (Blueprint $table): void {
            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table): void {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
