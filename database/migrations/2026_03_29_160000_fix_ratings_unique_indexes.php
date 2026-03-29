<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = Schema::getConnection();

        $oldUniqueExists = !empty($connection->select(
            "SHOW INDEX FROM ratings WHERE Key_name = 'ratings_job_id_unique'"
        ));

        if ($oldUniqueExists) {
            DB::statement('ALTER TABLE ratings DROP INDEX ratings_job_id_unique');
        }

        $newUniqueExists = !empty($connection->select(
            "SHOW INDEX FROM ratings WHERE Key_name = 'ratings_job_rated_by_unique'"
        ));

        if (!$newUniqueExists) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->unique(['job_id', 'rated_by_user_id'], 'ratings_job_rated_by_unique');
            });
        }

        $ratedUserIndexExists = !empty($connection->select(
            "SHOW INDEX FROM ratings WHERE Key_name = 'ratings_rated_user_id_index'"
        ));

        if (!$ratedUserIndexExists && Schema::hasColumn('ratings', 'rated_user_id')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->index('rated_user_id');
            });
        }
    }

    public function down(): void
    {
        $connection = Schema::getConnection();

        $newUniqueExists = !empty($connection->select(
            "SHOW INDEX FROM ratings WHERE Key_name = 'ratings_job_rated_by_unique'"
        ));

        if ($newUniqueExists) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropUnique('ratings_job_rated_by_unique');
            });
        }

        $ratedUserIndexExists = !empty($connection->select(
            "SHOW INDEX FROM ratings WHERE Key_name = 'ratings_rated_user_id_index'"
        ));

        if ($ratedUserIndexExists) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropIndex(['rated_user_id']);
            });
        }

        $oldUniqueExists = !empty($connection->select(
            "SHOW INDEX FROM ratings WHERE Key_name = 'ratings_job_id_unique'"
        ));

        if (!$oldUniqueExists) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->unique('job_id', 'ratings_job_id_unique');
            });
        }
    }
};
