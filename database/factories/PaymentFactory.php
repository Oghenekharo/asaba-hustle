<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\ServiceJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_id' => ServiceJob::factory(),
            'user_id' => User::factory()->client(),
            'amount' => fake()->randomFloat(2, 5000, 150000),
            'payment_method' => fake()->randomElement(['cash', 'transfer']),
            'reference' => 'AH_' . Str::upper(Str::random(12)),
            'status' => Payment::STATUS_PENDING,
            'idempotency_key' => (string) Str::uuid(),
            'verified_at' => null,
            'provider_payload' => [],
        ];
    }

    public function forJob(ServiceJob $job, ?User $client = null): static
    {
        return $this->state(fn () => [
            'job_id' => $job->id,
            'user_id' => $client?->id ?? $job->user_id,
            'amount' => $job->agreed_amount ?? $job->budget,
            'payment_method' => $job->payment_method,
        ]);
    }

    public function successful(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_SUCCESSFUL,
            'verified_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_FAILED,
            'verified_at' => now(),
        ]);
    }
}
