<?php

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\ServiceJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_id' => ServiceJob::factory(),
            'user_id' => User::factory()->worker(),
            'message' => fake()->sentence(),
            'status' => 'pending',
        ];
    }

    public function forJob(ServiceJob $job, ?User $worker = null): static
    {
        return $this->state(fn () => [
            'job_id' => $job->id,
            'user_id' => $worker?->id ?? User::factory()->worker(),
        ]);
    }
}
