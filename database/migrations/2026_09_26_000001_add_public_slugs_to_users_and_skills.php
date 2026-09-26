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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('slug')->nullable();
        });

        Schema::table('skills', function (Blueprint $table): void {
            $table->string('slug')->nullable();
        });

        $this->backfillSlugs('users', 'name');
        $this->backfillSlugs('skills', 'name');

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('slug');
        });

        Schema::table('skills', function (Blueprint $table): void {
            $table->unique('slug');
        });
    }

    private function backfillSlugs(string $table, string $sourceColumn): void
    {
        DB::table($table)
            ->select(['id', $sourceColumn])
            ->orderBy('id')
            ->chunkById(200, function ($records) use ($table, $sourceColumn): void {
                foreach ($records as $record) {
                    $base = Str::slug($record->{$sourceColumn}) ?: rtrim($table, 's');

                    do {
                        $slug = $base . '-' . Str::lower(Str::random(6));
                        $exists = DB::table($table)->where('slug', $slug)->exists();
                    } while ($exists);

                    DB::table($table)->where('id', $record->id)->update(['slug' => $slug]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('skills', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
