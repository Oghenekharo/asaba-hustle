<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitializePaymentRequest;
use App\Http\Requests\VerifyFlutterwavePaymentRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\ServiceJob;
use App\Services\PaymentService;
use DomainException;
use Illuminate\Http\Client\RequestException;

class WebPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function initializePaystack(InitializePaymentRequest $request)
    {
        $job = ServiceJob::findOrFail($request->validated('job_id'));
        $this->authorize('initialize', [Payment::class, $job]);

        if ($job->payment_method !== 'paystack') {
            return $this->errorResponse('This job does not use Paystack', 400);
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
            return $this->errorResponse('Unable to initialize payment at this time.', 502);
        }

        return $this->successResponse([
            'authorization_url' => data_get($payment->provider_payload, 'authorization_url'),
            'reference' => $payment->reference,
            'status' => $payment->status,
        ], 'Payment initialized successfully.');
    }

    public function verifyPaystack(VerifyPaymentRequest $request)
    {
        $payment = Payment::query()
            ->where('reference', $request->validated('reference'))
            ->first();

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

    public function initializeFlutterwave(InitializePaymentRequest $request)
    {
        $job = ServiceJob::findOrFail($request->validated('job_id'));
        $this->authorize('initialize', [Payment::class, $job]);

        if ($job->payment_method !== 'flutterwave') {
            return $this->errorResponse('This job does not use Flutterwave', 400);
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
            return $this->errorResponse('Unable to initialize Flutterwave payment at this time.', 502);
        }

        return $this->successResponse([
            'payment_link' => data_get($payment->provider_payload, 'link'),
            'reference' => $payment->reference,
            'status' => $payment->status,
        ], 'Flutterwave payment initialized successfully.');
    }

    public function verifyFlutterwave(VerifyFlutterwavePaymentRequest $request)
    {
        $payment = Payment::query()
            ->where('reference', $request->validated('tx_ref'))
            ->first();

        if (!$payment) {
            return $this->errorResponse('Payment record not found', 404);
        }

        $this->authorize('verify', $payment);

        try {
            $payment = $this->paymentService->verifyFlutterwavePayment(
                $payment,
                (int) $request->validated('transaction_id')
            );
        } catch (RequestException $exception) {
            return $this->errorResponse('Verification failed', 502);
        }

        return $this->successResponse(
            new PaymentResource($payment),
            'Flutterwave payment verified successfully.'
        );
    }
}
