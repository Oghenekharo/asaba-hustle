<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ratings', 'rated_by_user_id')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->foreignId('rated_by_user_id')->nullable()->after('worker_id')->constrained('users')->cascadeOnDelete();
            });
        }

        if (!Schema::hasColumn('ratings', 'rated_user_id')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->foreignId('rated_user_id')->nullable()->after('rated_by_user_id')->constrained('users')->cascadeOnDelete();
            });
        }

        if (!Schema::hasColumn('ratings', 'rated_by_role')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->string('rated_by_role', 20)->nullable()->after('rated_user_id');
            });
        }

        if (!Schema::hasColumn('ratings', 'rated_role')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->string('rated_role', 20)->nullable()->after('rated_by_role');
            });
        }

        DB::table('ratings')->update([
            'rated_by_user_id' => DB::raw('client_id'),
            'rated_user_id' => DB::raw('worker_id'),
            'rated_by_role' => 'client',
            'rated_role' => 'worker',
        ]);

        try {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropUnique('ratings_job_id_unique');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::table('ratings', function (Blueprint $table) {
                $table->unique(['job_id', 'rated_by_user_id'], 'ratings_job_rated_by_unique');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::table('ratings', function (Blueprint $table) {
                $table->index('rated_user_id');
            });
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
        try {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropUnique('ratings_job_rated_by_unique');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropIndex(['rated_user_id']);
            });
        } catch (\Throwable) {
        }

        if (Schema::hasColumn('ratings', 'rated_by_user_id')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropConstrainedForeignId('rated_by_user_id');
            });
        }

        if (Schema::hasColumn('ratings', 'rated_user_id')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropConstrainedForeignId('rated_user_id');
            });
        }

        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn('ratings', 'rated_by_role') ? 'rated_by_role' : null,
            Schema::hasColumn('ratings', 'rated_role') ? 'rated_role' : null,
        ]));

        if ($columnsToDrop !== []) {
            Schema::table('ratings', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        try {
            Schema::table('ratings', function (Blueprint $table) {
                $table->unique('job_id');
            });
        } catch (\Throwable) {
        }
    }
};
