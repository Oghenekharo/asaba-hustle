<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FlutterwaveWebhookRequest;
use App\Http\Requests\InitializePaymentRequest;
use App\Http\Requests\VerifyFlutterwavePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\ServiceJob;
use App\Services\PaymentService;
use DomainException;
use Illuminate\Http\Client\RequestException;

class FlutterwavePaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
    ) {
    }

    public function initialize(InitializePaymentRequest $request)
    {
        $data = $request->validated();
        $job = ServiceJob::findOrFail($data['job_id']);
        $this->authorize('initialize', [Payment::class, $job]);

        if ($job->payment_method !== 'flutterwave') {
            return $this->errorResponse(
                'This job does not use Flutterwave',
                400
            );
        }

        try {
            $payment = $this->paymentService->initializeFlutterwavePayment(
                $request->user(),
                $job,
                $request->header('Idempotency-Key')
            );
        } catch (DomainException $exception) {
            return $this->errorResponse($exception->getMessage(), 409);
        } catch (RequestException $exception) {
            return $this->errorResponse(
                'Unable to initialize Flutterwave payment at this time.',
                502
            );
        }

        return $this->successResponse([
            'payment_link' => data_get($payment->provider_payload, 'link'),
            'reference' => $payment->reference,
            'status' => $payment->status,
        ], 'Flutterwave payment initialized successfully.');
    }

    public function verify(VerifyFlutterwavePaymentRequest $request)
    {
        $data = $request->validated();
        $payment = Payment::where('reference', $data['tx_ref'])->first();

        if (!$payment) {
            return $this->errorResponse('Payment record not found', 404);
        }

        $this->authorize('verify', $payment);

        try {
            $payment = $this->paymentService->verifyFlutterwavePayment(
                $payment,
                (int) $data['transaction_id']
            );
        } catch (RequestException $exception) {
            return $this->errorResponse('Verification failed', 502);
        }

        return $this->successResponse(
            new PaymentResource($payment),
            'Flutterwave payment verified successfully.'
        );
    }

    public function webhook(FlutterwaveWebhookRequest $request)
    {
        if (!$request->hasValidSignature()) {
            return $this->errorResponse('Invalid webhook signature.', 401);
        }

        $this->paymentService->handleFlutterwaveWebhook($request->validated());

        return $this->successResponse(null, 'Flutterwave webhook received.');
    }
}
