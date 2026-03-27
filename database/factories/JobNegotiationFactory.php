<?php

namespace Database\Factories;

use App\Models\JobNegotiation;
use App\Models\ServiceJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobNegotiation>
 */
class JobNegotiationFactory extends Factory
{
    protected $model = JobNegotiation::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 5000, 150000);

        return [
            'job_id' => ServiceJob::factory(),
            'client_id' => User::factory()->client(),
            'worker_id' => User::factory()->worker(),
            'amount' => $amount,
            'message' => fake()->sentence(),
            'history' => [],
            'status' => 'pending',
            'created_by' => 'worker',
            'expires_at' => now()->addDays(3),
        ];
    }

    public function forJob(ServiceJob $job, ?User $client = null, ?User $worker = null): static
    {
        return $this->state(fn () => [
            'job_id' => $job->id,
            'client_id' => $client?->id ?? $job->user_id,
            'worker_id' => $worker?->id ?? $job->assigned_to ?? User::factory()->worker(),
        ]);
    }

    public function createdByClient(): static
    {
        return $this->state(fn () => [
            'created_by' => 'client',
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn () => [
            'status' => 'accepted',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
        ]);
    }
}
