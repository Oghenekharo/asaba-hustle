<?php

namespace Tests\Feature\Api;

use App\Models\Conversation;
use App\Models\ServiceJob;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_user_cannot_access_authenticated_api_routes(): void
    {
        $user = User::factory()->create([
            'account_status' => 'suspended',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/auth/me')
            ->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Your account has been suspended.',
            ]);
    }

    public function test_non_participant_cannot_view_conversation_messages(): void
    {
        $client = User::factory()->create();
        $worker = User::factory()->create();
        $intruder = User::factory()->create();
        $skill = Skill::create(['name' => 'Plumber']);

        $job = ServiceJob::create([
            'user_id' => $client->id,
            'skill_id' => $skill->id,
            'title' => 'Fix sink',
            'description' => 'Kitchen sink repair',
            'budget' => 15000,
            'location' => 'Asaba',
            'latitude' => 6.2,
            'longitude' => 6.73,
            'payment_method' => 'cash',
            'status' => ServiceJob::STATUS_ASSIGNED,
            'assigned_to' => $worker->id,
        ]);

        $conversation = Conversation::create([
            'job_id' => $job->id,
            'client_id' => $client->id,
            'worker_id' => $worker->id,
        ]);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/messages/conversation/{$conversation->uuid}")
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_non_owner_cannot_initialize_payment_for_a_job(): void
    {
        Role::create(['name' => 'client', 'guard_name' => 'web']);

        $owner = User::factory()->create();
        $owner->assignRole('client');

        $intruder = User::factory()->create();
        $intruder->assignRole('client');

        $skill = Skill::create(['name' => 'Electrician']);

        $job = ServiceJob::create([
            'user_id' => $owner->id,
            'skill_id' => $skill->id,
            'title' => 'Wire generator',
            'description' => 'Generator wiring work',
            'budget' => 25000,
            'location' => 'Asaba',
            'latitude' => 6.2,
            'longitude' => 6.73,
            'payment_method' => 'paystack',
            'status' => ServiceJob::STATUS_OPEN,
        ]);

        Sanctum::actingAs($intruder);

        $this->postJson('/api/payments/initialize', [
            'job_id' => $job->id,
        ])->assertForbidden()
            ->assertJsonPath('success', false);
    }
}
