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
        Schema::create('service_jobs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('skill_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');

            $table->text('description');

            $table->decimal('budget', 10, 2);

            $table->string('location');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->enum('payment_method', [
                'cash',
                'paystack',
                'flutterwave'
            ]);

            // $table->enum('status', [
            //     'open',
            //     'applied',
            //     'assigned',
            //     'in_progress',
            //     'completed',
            //     'cancelled'
            // ])->default('open');

            $table->string('status')->default('open');

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['latitude', 'longitude']);
            $table->index('skill_id');
            $table->index('status');

            $table->index(['assigned_to', 'status'], 'worker_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_jobs');
    }
};
