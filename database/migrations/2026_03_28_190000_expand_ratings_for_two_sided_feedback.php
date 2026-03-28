<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->foreignId('rated_by_user_id')->nullable()->after('worker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rated_user_id')->nullable()->after('rated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('rated_by_role', 20)->nullable()->after('rated_user_id');
            $table->string('rated_role', 20)->nullable()->after('rated_by_role');
        });

        DB::table('ratings')->update([
            'rated_by_user_id' => DB::raw('client_id'),
            'rated_user_id' => DB::raw('worker_id'),
            'rated_by_role' => 'client',
            'rated_role' => 'worker',
        ]);

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropUnique('ratings_job_id_unique');
            $table->unique(['job_id', 'rated_by_user_id'], 'ratings_job_rated_by_unique');
            $table->index('rated_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropUnique('ratings_job_rated_by_unique');
            $table->dropIndex(['rated_user_id']);
            $table->dropConstrainedForeignId('rated_by_user_id');
            $table->dropConstrainedForeignId('rated_user_id');
            $table->dropColumn(['rated_by_role', 'rated_role']);
            $table->unique('job_id');
        });
    }
};
