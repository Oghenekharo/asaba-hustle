<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')
                ->constrained('service_jobs')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('worker_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('rating', 3, 2)->default(0.0);

            $table->text('review')->nullable();

            $table->unique('job_id'); // prevent duplicate rating

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
