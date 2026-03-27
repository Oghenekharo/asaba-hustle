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
        Schema::create('job_negotiations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')->constrained('service_jobs')->cascadeOnDelete();

            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->text('message')->nullable();

            $table->enum('status', [
                'pending',     // waiting for response
                'countered',   // new offer made
                'accepted',
                'rejected'
            ])->default('pending');

            $table->enum('created_by', [
                'client',
                'worker'
            ]);

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_negotiations');
    }
};
