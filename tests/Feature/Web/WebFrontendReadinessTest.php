<?php

namespace Tests\Feature\Web;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\ServiceJob;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserNotification;
use App\Http\Middleware\EnsureSessionIsActive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebFrontendReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_through_the_web_json_endpoint(): void
    {
        Role::create(['name' => 'client', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'phone' => '08000000099',
            'password' => Hash::make('password123'),
        ]);

        $user->assignRole('client');

        $response = $this->postJson('/login', [
            'phone' => '08000000099',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.redirect', route('web.app'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_view_jobs_page_with_job_content(): void
    {
        Role::create(['name' => 'client', 'guard_name' => 'web']);

        $skill = Skill::factory()->create([
            'name' => 'Cleaning',
        ]);

        $user = User::factory()->create();
        $user->assignRole('client');

        ServiceJob::factory()->create([
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'title' => 'Urgent apartment cleaning',
            'status' => ServiceJob::STATUS_OPEN,
        ]);

        $this->actingAs($user)
            ->get('/app/jobs')
            ->assertOk()
            ->assertSee('Urgent apartment cleaning')
            ->assertSee('Cleaning');
    }

    public function test_authenticated_user_can_update_profile_through_web_json_endpoint(): void
    {
        Role::create(['name' => 'client', 'guard_name' => 'web']);
        $skill = Skill::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('client');

        $this->actingAs($user)
            ->putJson('/app/profile', [
                'name' => 'Updated Web User',
                'bio' => 'Now editable from the Blade frontend.',
                'primary_skill_id' => $skill->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Web User')
            ->assertJsonPath('data.bio', 'Now editable from the Blade frontend.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Web User',
        ]);
    }

    public function test_worker_can_apply_for_job_through_web_json_endpoint(): void
    {
        Role::create(['name' => 'client', 'guard_name' => 'web']);
        Role::create(['name' => 'worker', 'guard_name' => 'web']);

        $client = User::factory()->create();
        $client->assignRole('client');

        $worker = User::factory()->create([
            'availability_status' => 'available',
        ]);
        $worker->assignRole('worker');

        $job = ServiceJob::factory()->create([
            'user_id' => $client->id,
            'status' => ServiceJob::STATUS_OPEN,
        ]);

        $this->actingAs($worker)
            ->postJson("/app/jobs/{$job->id}/apply", [
                'message' => 'I can handle this quickly.',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('job_applications', [
            'job_id' => $job->id,
            'user_id' => $worker->id,
            'message' => 'I can handle this quickly.',
        ]);
    }

    public function test_authenticated_user_can_fetch_notifications_through_web_json_endpoint(): void
    {
        Role::create(['name' => 'client', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('client');

        UserNotification::factory()->create([
            'user_id' => $user->id,
            'title' => 'New message',
            'action_url' => '/app/conversations?conversation=test-uuid',
            'action_label' => 'Open Chat',
        ]);

        $this->actingAs($user)
            ->getJson('/app/notifications')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.title', 'New message')
            ->assertJsonPath('data.0.action_label', 'Open Chat');
    }

    public function test_authenticated_user_can_view_notifications_page_with_cursor_navigation(): void
    {
        Role::create(['name' => 'client', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('client');

        UserNotification::factory()->count(25)->sequence(
            fn ($sequence) => [
                'user_id' => $user->id,
                'title' => 'Notification ' . ($sequence->index + 1),
                'message' => 'Activity item ' . ($sequence->index + 1),
            ],
        )->create();

        $this->actingAs($user)
            ->get('/app/notifications')
            ->assertOk()
            ->assertSee('All Notifications')
            ->assertSee('Cursor Pagination')
            ->assertSee('Notification 25');
    }

    public function test_conversations_are_sorted_by_latest_message_activity_on_messages_page(): void
    {
        Role::create(['name' => 'client', 'guard_name' => 'web']);
        Role::create(['name' => 'worker', 'guard_name' => 'web']);

        $client = User::factory()->create();
        $client->assignRole('client');

        $workerA = User::factory()->create();
        $workerA->assignRole('worker');

        $workerB = User::factory()->create();
        $workerB->assignRole('worker');

        $jobA = ServiceJob::factory()->create([
            'user_id' => $client->id,
            'assigned_to' => $workerA->id,
            'status' => ServiceJob::STATUS_ASSIGNED,
        ]);

        $jobB = ServiceJob::factory()->create([
            'user_id' => $client->id,
            'assigned_to' => $workerB->id,
            'status' => ServiceJob::STATUS_ASSIGNED,
        ]);

        $conversationA = Conversation::create([
            'job_id' => $jobA->id,
            'client_id' => $client->id,
            'worker_id' => $workerA->id,
        ]);

        $conversationB = Conversation::create([
            'job_id' => $jobB->id,
            'client_id' => $client->id,
            'worker_id' => $workerB->id,
        ]);

        ChatMessage::create([
            'conversation_id' => $conversationA->id,
            'sender_id' => $workerA->id,
            'message' => 'Older conversation message',
            'created_at' => now()->subMinutes(20),
            'updated_at' => now()->subMinutes(20),
        ]);

        ChatMessage::create([
            'conversation_id' => $conversationB->id,
            'sender_id' => $workerB->id,
            'message' => 'Newest conversation message',
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($client)->get('/app/conversations');

        $response->assertOk();
        $response->assertSeeInOrder([
            $workerB->name,
            $workerA->name,
        ]);
    }

    public function test_session_inactivity_middleware_logs_out_after_thirty_minutes(): void
    {
        Role::create(['name' => 'client', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('client');

        $request = Request::create('/app', 'GET');
        $session = app('session.store');
        $session->start();
        $session->put('last_activity_at', now()->subMinutes(31)->timestamp);
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => $user);

        Auth::login($user);

        $response = app(EnsureSessionIsActive::class)->handle(
            $request,
            fn () => response('ok'),
        );

        $this->assertTrue($response->isRedirect(route('login')));
        $this->assertGuest();
    }
}
