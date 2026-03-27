<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitializePaymentRequest;
use App\Http\Requests\PaystackWebhookRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\ServiceJob;
use App\Services\PaymentService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    /**
     * Initialize Paystack payment
     */
    public function initialize(InitializePaymentRequest $request)
    {
        $data = $request->validated();
        $job = ServiceJob::findOrFail($data['job_id']);
        $this->authorize('initialize', [Payment::class, $job]);

        if ($job->payment_method !== 'paystack') {
            return $this->errorResponse(
                'This job does not use Paystack',
                400
            );
        }

        try {
            $payment = $this->paymentService->initializePayment(
                $request->user(),
                $job,
                $request->header('Idempotency-Key')
            );
        } catch (DomainException $exception) {
            return $this->errorResponse($exception->getMessage(), 409);
        } catch (RequestException $exception) {
            return $this->errorResponse(
                'Unable to initialize payment at this time.',
                502
            );
        }

        return $this->successResponse([
            'authorization_url' => data_get($payment->provider_payload, 'authorization_url'),
            'reference' => $payment->reference,
            'status' => $payment->status,
        ], 'Payment initialized successfully.');
    }


    /**
     * Verify Paystack payment
     */
    public function verify(VerifyPaymentRequest $request)
    {
        $reference = $request->validated('reference');

        $payment = Payment::where('reference', $reference)->first();

        if (!$payment) {
            return $this->errorResponse('Payment record not found', 404);
        }

        $this->authorize('verify', $payment);

        try {
            $payment = $this->paymentService->verifyPayment($payment);
        } catch (RequestException $exception) {
            return $this->errorResponse('Verification failed', 502);
        }

        return $this->successResponse(
            new PaymentResource($payment),
            'Payment verified successfully.'
        );
    }

    public function webhook(PaystackWebhookRequest $request)
    {
        if (!$request->hasValidSignature()) {
            return $this->errorResponse('Invalid webhook signature.', 401);
        }

        $this->paymentService->handleWebhook($request->validated());

        return $this->successResponse(null, 'Webhook received.');
    }
}
