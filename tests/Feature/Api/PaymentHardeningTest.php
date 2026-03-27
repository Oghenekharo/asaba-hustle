<?php

namespace Tests\Feature\Api;

use App\Models\Payment;
use App\Models\ServiceJob;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_initialization_is_idempotent_for_the_same_key(): void
    {
        config()->set('services.paystack.secret', 'test-secret');
        config()->set('services.paystack.base_url', 'https://api.paystack.test');

        Role::create(['name' => 'client', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('client');

        $skill = Skill::create(['name' => 'Painter']);

        $job = ServiceJob::create([
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'title' => 'Paint apartment',
            'description' => 'Two bedroom repaint',
            'budget' => 50000,
            'location' => 'Asaba',
            'latitude' => 6.2,
            'longitude' => 6.73,
            'payment_method' => 'paystack',
            'status' => ServiceJob::STATUS_OPEN,
        ]);

        Http::fake([
            'https://api.paystack.test/transaction/initialize' => Http::response([
                'data' => [
                    'authorization_url' => 'https://paystack.test/authorize/abc123',
                ],
            ], 200),
        ]);

        Sanctum::actingAs($user);

        $first = $this->withHeaders([
            'Idempotency-Key' => 'idem-key-123',
        ])->postJson('/api/payments/initialize', [
            'job_id' => $job->id,
        ]);

        $second = $this->withHeaders([
            'Idempotency-Key' => 'idem-key-123',
        ])->postJson('/api/payments/initialize', [
            'job_id' => $job->id,
        ]);

        $first->assertOk();
        $second->assertOk();
        $first->assertJsonPath('success', true);
        $second->assertJsonPath('success', true);

        $this->assertSame(
            $first->json('data.reference'),
            $second->json('data.reference')
        );

        Http::assertSentCount(1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_signed_paystack_webhook_marks_payment_successful(): void
    {
        config()->set('services.paystack.secret', 'test-secret');

        $user = User::factory()->create();
        $skill = Skill::create(['name' => 'Welder']);

        $job = ServiceJob::create([
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'title' => 'Gate welding',
            'description' => 'Fix front gate',
            'budget' => 75000,
            'location' => 'Asaba',
            'latitude' => 6.2,
            'longitude' => 6.73,
            'payment_method' => 'paystack',
            'status' => ServiceJob::STATUS_OPEN,
        ]);

        $payment = Payment::create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'amount' => $job->budget,
            'payment_method' => 'paystack',
            'reference' => 'AH_TESTREFERENCE',
            'status' => Payment::STATUS_PENDING,
        ]);

        $payload = json_encode([
            'event' => 'charge.success',
            'data' => [
                'reference' => $payment->reference,
                'status' => 'success',
                'amount' => 7500000,
            ],
        ], JSON_THROW_ON_ERROR);

        $signature = hash_hmac('sha512', $payload, 'test-secret');

        $response = $this->call(
            'POST',
            '/api/payments/webhook/paystack',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
            ],
            $payload
        );

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_SUCCESSFUL,
        ]);

        $this->assertNotNull($payment->fresh()->verified_at);
    }
}
