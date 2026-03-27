<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaystackWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event' => 'nullable|string|max:255',
            'data' => 'required|array',
            'data.reference' => 'required|string|max:255',
            'data.status' => 'nullable|string|max:255',
        ];
    }

    public function hasValidSignature(): bool
    {
        $signature = (string) $this->header('X-Paystack-Signature', '');
        $secret = (string) config('services.paystack.secret');
        $computed = hash_hmac('sha512', $this->getContent(), $secret);

        return $signature !== '' && hash_equals($computed, $signature);
    }
}
