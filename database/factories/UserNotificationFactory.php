<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserNotification>
 */
class UserNotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'message' => fake()->sentence(),
            'type' => fake()->randomElement(['job', 'message', 'payment', 'system']),
            'action_url' => fake()->boolean(40) ? fake()->url() : null,
            'action_label' => fake()->boolean(40) ? fake()->randomElement(['View Job', 'Open Chat', 'Review']) : null,
            'is_read' => fake()->boolean(40),
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(fn () => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }
}
