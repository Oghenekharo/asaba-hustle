<?php

namespace Database\Factories;

use App\Models\ServiceJob;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceJob>
 */
class ServiceJobFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->client(),
            'skill_id' => Skill::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'budget' => fake()->randomFloat(2, 5000, 150000),
            'agreed_amount' => null,
            'location' => fake()->city() . ', Asaba',
            'latitude' => 6.20 + fake()->randomFloat(6, 0.001, 0.100),
            'longitude' => 6.70 + fake()->randomFloat(6, 0.001, 0.100),
            'payment_method' => fake()->randomElement(['cash', 'transfer', 'paystack', 'flutterwave']),
            'status' => ServiceJob::STATUS_OPEN,
            'assigned_to' => null,
            'paid_at' => null,
        ];
    }

    public function forClient(?User $client = null): static
    {
        return $this->state(fn () => [
            'user_id' => $client?->id ?? User::factory()->client(),
        ]);
    }

    public function assigned(?User $worker = null): static
    {
        return $this->state(fn () => [
            'status' => ServiceJob::STATUS_ASSIGNED,
            'assigned_to' => $worker?->id ?? User::factory()->worker(),
            'agreed_amount' => fake()->randomFloat(2, 5000, 150000),
        ]);
    }

    public function workerAccepted(?User $worker = null): static
    {
        return $this->state(fn () => [
            'status' => ServiceJob::STATUS_WORKER_ACCEPTED,
            'assigned_to' => $worker?->id ?? User::factory()->worker(),
            'agreed_amount' => fake()->randomFloat(2, 5000, 150000),
        ]);
    }

    public function inProgress(?User $worker = null): static
    {
        return $this->state(fn () => [
            'status' => ServiceJob::STATUS_IN_PROGRESS,
            'assigned_to' => $worker?->id ?? User::factory()->worker(),
            'agreed_amount' => fake()->randomFloat(2, 5000, 150000),
        ]);
    }

    public function completed(?User $worker = null): static
    {
        return $this->state(fn () => [
            'status' => ServiceJob::STATUS_COMPLETED,
            'assigned_to' => $worker?->id ?? User::factory()->worker(),
            'agreed_amount' => fake()->randomFloat(2, 5000, 150000),
            'paid_at' => now(),
        ]);
    }

    public function rated(?User $worker = null): static
    {
        return $this->state(fn () => [
            'status' => ServiceJob::STATUS_RATED,
            'assigned_to' => $worker?->id ?? User::factory()->worker(),
            'agreed_amount' => fake()->randomFloat(2, 5000, 150000),
        ]);
    }
}
