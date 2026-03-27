<?php

namespace Database\Factories;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => User::factory(),
            'message' => fake()->sentence(),
            'is_read' => fake()->boolean(60),
        ];
    }

    public function forConversation(Conversation $conversation, ?User $sender = null): static
    {
        return $this->state(fn () => [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender?->id ?? fake()->randomElement([$conversation->client_id, $conversation->worker_id]),
        ]);
    }
}
