<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'name' => trim($firstName . ' ' . $lastName),
            'phone' => fake()->unique()->numerify('080########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'primary_skill_id' => Skill::query()->inRandomOrder()->value('id'),
            'availability_status' => 'available',
            'account_status' => 'active',
            'is_verified' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function verifiedPhone(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone_verified_at' => now(),
            'verification_channel' => null,
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ]);
    }

    public function verifiedEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
            'verification_channel' => null,
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ]);
    }

    public function client(): static
    {
        return $this
            ->state(fn () => [
                'availability_status' => 'available',
                'account_status' => 'active',
                'bio' => fake()->sentence(10),
                'rating' => null,
            ])
            ->afterCreating(function ($user) {
                $user->syncRoles(['client']);
            });
    }

    public function worker(): static
    {
        return $this
            ->state(fn () => [
                'availability_status' => fake()->randomElement(['available', 'busy']),
                'account_status' => 'active',
                'bio' => fake()->sentence(12),
                'rating' => fake()->randomFloat(2, 3.8, 5.0),
            ])
            ->afterCreating(function ($user) {
                $user->syncRoles(['worker']);
            });
    }

    public function admin(): static
    {
        return $this->afterCreating(function ($user) {
            $user->syncRoles(['admin']);
        });
    }

    public function named(string $firstName, string $lastName): static
    {
        return $this->state(fn () => [
            'name' => trim($firstName . ' ' . $lastName),
            'email' => fake()->unique()->safeEmail(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_status' => 'suspended',
        ]);
    }

    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_status' => 'banned',
        ]);
    }
}
