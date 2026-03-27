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

class FlutterwavePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_flutterwave_payment_initialization_is_idempotent_for_the_same_key(): void
    {
        config()->set('services.flutterwave.secret', 'flutterwave-secret');
        config()->set('services.flutterwave.base_url', 'https://api.flutterwave.test/v3');
        config()->set('services.flutterwave.redirect_url', 'https://example.com/payments/flutterwave/callback');

        Role::create(['name' => 'client', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('client');

        $skill = Skill::create(['name' => 'Mechanic']);

        $job = ServiceJob::create([
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'title' => 'Engine fix',
            'description' => 'Repair engine issue',
            'budget' => 60000,
            'location' => 'Asaba',
            'latitude' => 6.2,
            'longitude' => 6.73,
            'payment_method' => 'flutterwave',
            'status' => ServiceJob::STATUS_OPEN,
        ]);

        Http::fake([
            'https://api.flutterwave.test/v3/payments' => Http::response([
                'data' => [
                    'link' => 'https://flutterwave.test/pay/xyz789',
                ],
            ], 200),
        ]);

        Sanctum::actingAs($user);

        $first = $this->withHeaders([
            'Idempotency-Key' => 'flutterwave-idem-123',
        ])->postJson('/api/payments/flutterwave/initialize', [
            'job_id' => $job->id,
        ]);

        $second = $this->withHeaders([
            'Idempotency-Key' => 'flutterwave-idem-123',
        ])->postJson('/api/payments/flutterwave/initialize', [
            'job_id' => $job->id,
        ]);

        $first->assertOk()->assertJsonPath('success', true);
        $second->assertOk()->assertJsonPath('success', true);

        $this->assertSame(
            $first->json('data.reference'),
            $second->json('data.reference')
        );

        Http::assertSentCount(1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'flutterwave',
        ]);
    }

    public function test_signed_flutterwave_webhook_marks_payment_successful(): void
    {
        config()->set('services.flutterwave.webhook_secret_hash', 'flutterwave-hash');

        $user = User::factory()->create();
        $skill = Skill::create(['name' => 'Technician']);

        $job = ServiceJob::create([
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'title' => 'AC service',
            'description' => 'Repair AC fault',
            'budget' => 45000,
            'location' => 'Asaba',
            'latitude' => 6.2,
            'longitude' => 6.73,
            'payment_method' => 'flutterwave',
            'status' => ServiceJob::STATUS_OPEN,
        ]);

        $payment = Payment::create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'amount' => $job->budget,
            'payment_method' => 'flutterwave',
            'reference' => 'AH_FLWREFERENCE',
            'status' => Payment::STATUS_PENDING,
        ]);

        $payload = [
            'event' => 'charge.completed',
            'data' => [
                'id' => 123456,
                'tx_ref' => $payment->reference,
                'status' => 'successful',
            ],
        ];

        $response = $this->postJson(
            '/api/payments/webhook/flutterwave',
            $payload,
            ['verif-hash' => 'flutterwave-hash']
        );

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_SUCCESSFUL,
        ]);
    }
}
