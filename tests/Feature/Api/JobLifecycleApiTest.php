<?php

namespace Tests\Feature\Api;

use App\Events\JobStatusUpdated;
use App\Models\Rating;
use App\Models\ServiceJob;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JobLifecycleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'client', 'guard_name' => 'web']);
        Role::create(['name' => 'worker', 'guard_name' => 'web']);
    }

    public function test_worker_can_mark_job_completed_and_dispatch_status_update(): void
    {
        Event::fake([JobStatusUpdated::class]);

        [$client, $worker, $job] = $this->makeAssignedJob(ServiceJob::STATUS_IN_PROGRESS);

        Sanctum::actingAs($worker);

        $this->postJson("/api/jobs/{$job->id}/complete")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', ServiceJob::STATUS_PAYMENT_PENDING);

        $this->assertDatabaseHas('service_jobs', [
            'id' => $job->id,
            'status' => ServiceJob::STATUS_PAYMENT_PENDING,
        ]);

        Event::assertDispatched(JobStatusUpdated::class, function (JobStatusUpdated $event) use ($job) {
            return $event->job->id === $job->id
                && $event->job->status === ServiceJob::STATUS_PAYMENT_PENDING;
        });
    }

    public function test_client_can_mark_payment_as_sent_and_worker_can_confirm_payment(): void
    {
        Event::fake([JobStatusUpdated::class]);

        [$client, $worker, $job] = $this->makeAssignedJob(ServiceJob::STATUS_PAYMENT_PENDING);

        Sanctum::actingAs($client);

        $this->postJson("/api/jobs/{$job->id}/mark-paid")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', ServiceJob::STATUS_PAYMENT_PENDING);

        $this->assertNotNull($job->fresh()->paid_at);

        Sanctum::actingAs($worker);

        $this->postJson("/api/jobs/{$job->id}/confirm-payment")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', ServiceJob::STATUS_COMPLETED);

        $freshJob = $job->fresh();

        $this->assertSame(ServiceJob::STATUS_COMPLETED, $freshJob->status);
        $this->assertSame('available', $worker->fresh()->availability_status);

        Event::assertDispatched(JobStatusUpdated::class, 2);
    }

    public function test_client_can_rate_completed_job_and_worker_rating_is_recomputed(): void
    {
        Event::fake([JobStatusUpdated::class]);

        [$client, $worker] = $this->makeUsers();
        $skill = Skill::factory()->create();

        Rating::create([
            'job_id' => ServiceJob::factory()->create([
                'user_id' => $client->id,
                'skill_id' => $skill->id,
                'status' => ServiceJob::STATUS_RATED,
                'assigned_to' => $worker->id,
                'paid_at' => now(),
            ])->id,
            'client_id' => $client->id,
            'worker_id' => $worker->id,
            'rating' => 4,
            'review' => 'Solid work.',
        ]);

        $worker->syncAverageRating();

        $job = ServiceJob::factory()->create([
            'user_id' => $client->id,
            'skill_id' => $skill->id,
            'status' => ServiceJob::STATUS_COMPLETED,
            'assigned_to' => $worker->id,
            'paid_at' => now(),
        ]);

        Sanctum::actingAs($client);

        $this->postJson("/api/jobs/{$job->id}/rate", [
            'rating' => 5,
            'review' => 'Excellent finish and communication.',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.rating', 5);

        $this->assertDatabaseHas('ratings', [
            'job_id' => $job->id,
            'worker_id' => $worker->id,
            'rating' => 5,
        ]);

        $this->assertSame(ServiceJob::STATUS_RATED, $job->fresh()->status);
        $this->assertSame(4.5, (float) $worker->fresh()->rating);

        Event::assertDispatched(JobStatusUpdated::class, function (JobStatusUpdated $event) use ($job) {
            return $event->job->id === $job->id
                && $event->job->status === ServiceJob::STATUS_RATED;
        });
    }

    public function test_client_cannot_rate_job_before_payment_is_confirmed(): void
    {
        [$client, $worker, $job] = $this->makeAssignedJob(ServiceJob::STATUS_PAYMENT_PENDING);

        Sanctum::actingAs($client);

        $this->postJson("/api/jobs/{$job->id}/rate", [
            'rating' => 3,
        ])->assertForbidden();
    }

    public function test_phone_verification_does_not_make_user_admin_verified(): void
    {
        $user = User::factory()->create([
            'phone' => '08077778888',
            'is_verified' => false,
            'phone_verified_at' => null,
            'verification_channel' => 'phone',
            'verification_token' => bcrypt('123456'),
            'verification_token_expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/api/auth/verify-phone', [
            'phone' => '08077778888',
            'token' => '123456',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_verified', false);

        $freshUser = $user->fresh();

        $this->assertNotNull($freshUser->phone_verified_at);
        $this->assertFalse($freshUser->is_verified);
    }

    protected function makeAssignedJob(string $status): array
    {
        [$client, $worker] = $this->makeUsers();

        $job = ServiceJob::factory()->create([
            'user_id' => $client->id,
            'status' => $status,
            'assigned_to' => $worker->id,
            'paid_at' => $status === ServiceJob::STATUS_PAYMENT_PENDING ? null : null,
        ]);

        return [$client, $worker, $job];
    }

    protected function makeUsers(): array
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $worker = User::factory()->create([
            'availability_status' => 'busy',
        ]);
        $worker->assignRole('worker');

        return [$client, $worker];
    }
}
