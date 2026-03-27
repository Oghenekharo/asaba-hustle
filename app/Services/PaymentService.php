<?php

namespace App\Services;

use App\Events\PaymentCompleted;
use App\Models\Payment;
use App\Models\ServiceJob;
use App\Models\User;
use App\Support\CacheKeys;
use DomainException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected HttpFactory $http,
        protected ActivityLogService $activityLogService,
    ) {
    }

    public function initializePayment(User $user, ServiceJob $job, ?string $idempotencyKey = null): Payment
    {
        $existingPendingPayment = $this->guardAgainstDuplicatePayments($user, $job, 'paystack', $idempotencyKey);

        if ($existingPendingPayment) {
            return $existingPendingPayment;
        }

        $reference = $this->generateReference();
        $payableAmount = $job->agreed_amount ?? $job->budget;
        $amount = (int) round($payableAmount * 100);

        $response = $this->http
            ->withToken(config('services.paystack.secret'))
            ->post(config('services.paystack.base_url') . '/transaction/initialize', [
                'email' => $user->email ?: ($user->phone . '@asaba.local'),
                'amount' => $amount,
                'reference' => $reference,
            ]);

        if (!$response->successful()) {
            throw new RequestException($response);
        }

        $data = $response->json('data', []);

        $payment = Payment::create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'amount' => $payableAmount,
            'payment_method' => 'paystack',
            'reference' => $reference,
            'status' => Payment::STATUS_PENDING,
            'idempotency_key' => $idempotencyKey,
            'provider_payload' => $data,
        ]);

        $this->activityLogService->log($user->id, 'payment_initialized', [
            'job_id' => $job->id,
            'payment_id' => $payment->id,
            'reference' => $payment->reference,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
        ]);

        return $payment;
    }

    public function initializeFlutterwavePayment(User $user, ServiceJob $job, ?string $idempotencyKey = null): Payment
    {
        $existingPendingPayment = $this->guardAgainstDuplicatePayments($user, $job, 'flutterwave', $idempotencyKey);

        if ($existingPendingPayment) {
            return $existingPendingPayment;
        }

        $reference = $this->generateReference();

        $response = $this->http
            ->withToken(config('services.flutterwave.secret'))
            ->post(config('services.flutterwave.base_url') . '/payments', [
                'tx_ref' => $reference,
                'amount' => $job->agreed_amount ?? $job->budget,
                'currency' => config('services.flutterwave.currency', 'NGN'),
                'redirect_url' => config('services.flutterwave.redirect_url'),
                'customer' => [
                    'email' => $user->email ?: ($user->phone . '@asaba.local'),
                    'phonenumber' => $user->phone,
                    'name' => $user->name,
                ],
                'customizations' => [
                    'title' => 'Asaba Hustle Payment',
                    'description' => $job->title,
                ],
            ]);

        if (!$response->successful()) {
            throw new RequestException($response);
        }

        $data = $response->json('data', []);

        $payment = Payment::create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'amount' => $job->agreed_amount ?? $job->budget,
            'payment_method' => 'flutterwave',
            'reference' => $reference,
            'status' => Payment::STATUS_PENDING,
            'idempotency_key' => $idempotencyKey,
            'provider_payload' => $data,
        ]);

        $this->activityLogService->log($user->id, 'payment_initialized', [
            'job_id' => $job->id,
            'payment_id' => $payment->id,
            'reference' => $payment->reference,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
        ]);

        return $payment;
    }

    public function verifyPayment(Payment $payment): Payment
    {
        $response = $this->http
            ->withToken(config('services.paystack.secret'))
            ->get(config('services.paystack.base_url') . "/transaction/verify/{$payment->reference}");

        if (!$response->successful()) {
            throw new RequestException($response);
        }

        return $this->syncVerifiedPayment($payment, $response->json('data', []));
    }

    public function verifyFlutterwavePayment(Payment $payment, int $transactionId): Payment
    {
        $response = $this->http
            ->withToken(config('services.flutterwave.secret'))
            ->get(config('services.flutterwave.base_url') . "/transactions/{$transactionId}/verify");

        if (!$response->successful()) {
            throw new RequestException($response);
        }

        return $this->syncVerifiedPayment($payment, $response->json('data', []));
    }

    public function handleWebhook(array $payload): ?Payment
    {
        $reference = data_get($payload, 'data.reference');

        if (!$reference) {
            return null;
        }

        $payment = Payment::where('reference', $reference)->first();

        if (!$payment) {
            return null;
        }

        return $this->syncVerifiedPayment($payment, data_get($payload, 'data', []));
    }

    public function handleFlutterwaveWebhook(array $payload): ?Payment
    {
        $reference = data_get($payload, 'data.tx_ref');

        if (!$reference) {
            return null;
        }

        $payment = Payment::where('reference', $reference)
            ->where('payment_method', 'flutterwave')
            ->first();

        if (!$payment) {
            return null;
        }

        return $this->syncVerifiedPayment($payment, data_get($payload, 'data', []));
    }

    public function syncVerifiedPayment(Payment $payment, array $providerData): Payment
    {
        return DB::transaction(function () use ($payment, $providerData) {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $normalizedStatus = $this->normalizeProviderStatus((string) data_get($providerData, 'status', ''));
            $mergedPayload = array_filter([
                ...($payment->provider_payload ?? []),
                ...$providerData,
            ], fn ($value) => $value !== null);

            if ($normalizedStatus === Payment::STATUS_SUCCESSFUL && $payment->status !== Payment::STATUS_SUCCESSFUL) {
                $payment->update([
                    'status' => Payment::STATUS_SUCCESSFUL,
                    'verified_at' => now(),
                    'provider_payload' => $mergedPayload,
                ]);

                $this->activityLogService->log($payment->user_id, 'payment_verified_successful', [
                    'job_id' => $payment->job_id,
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                ]);

                DB::afterCommit(function () use ($payment) {
                    Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);
                    event(new PaymentCompleted($payment->fresh()));
                });

                return $payment->fresh();
            }

            if ($normalizedStatus === Payment::STATUS_FAILED && $payment->status === Payment::STATUS_PENDING) {
                $payment->update([
                    'status' => Payment::STATUS_FAILED,
                    'verified_at' => now(),
                    'provider_payload' => $mergedPayload,
                ]);

                $this->activityLogService->log($payment->user_id, 'payment_verified_failed', [
                    'job_id' => $payment->job_id,
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                ]);

                DB::afterCommit(function () {
                    Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);
                });

                return $payment->fresh();
            }

            $payment->update([
                'verified_at' => now(),
                'provider_payload' => $mergedPayload,
            ]);

            return $payment->fresh();
        });
    }

    protected function normalizeProviderStatus(string $status): string
    {
        return match (strtolower($status)) {
            'success', 'successful', 'completed' => Payment::STATUS_SUCCESSFUL,
            'failed', 'abandoned' => Payment::STATUS_FAILED,
            default => Payment::STATUS_PENDING,
        };
    }

    protected function guardAgainstDuplicatePayments(
        User $user,
        ServiceJob $job,
        string $paymentMethod,
        ?string $idempotencyKey
    ): ?Payment {
        $successfulPaymentExists = Payment::query()
            ->where('job_id', $job->id)
            ->where('status', Payment::STATUS_SUCCESSFUL)
            ->exists();

        if ($successfulPaymentExists) {
            throw new DomainException('Payment has already been completed for this job.');
        }

        $existingPendingPayment = Payment::query()
            ->where('job_id', $job->id)
            ->where('user_id', $user->id)
            ->where('payment_method', $paymentMethod)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

        if (!$existingPendingPayment) {
            return null;
        }

        if (
            $idempotencyKey === null ||
            $existingPendingPayment->idempotency_key === null ||
            hash_equals($existingPendingPayment->idempotency_key, $idempotencyKey)
        ) {
            return $existingPendingPayment;
        }

        throw new DomainException('A pending payment already exists for this job.');
    }

    protected function generateReference(): string
    {
        do {
            $reference = 'AH_' . Str::upper(Str::random(12));
        } while (Payment::where('reference', $reference)->exists());

        return $reference;
    }
}
