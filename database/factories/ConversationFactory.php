<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ServiceJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'job_id' => ServiceJob::factory(),
            'client_id' => User::factory()->client(),
            'worker_id' => User::factory()->worker(),
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
}
