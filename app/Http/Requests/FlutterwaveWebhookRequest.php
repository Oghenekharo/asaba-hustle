<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlutterwaveWebhookRequest extends FormRequest
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
            'data.id' => 'required|integer|min:1',
            'data.tx_ref' => 'required|string|max:255',
            'data.status' => 'nullable|string|max:255',
        ];
    }

    public function hasValidSignature(): bool
    {
        $hashHeader = (string) $this->header('verif-hash', '');
        $expectedHash = (string) config('services.flutterwave.webhook_secret_hash', '');

        if ($hashHeader !== '' && $expectedHash !== '' && hash_equals($expectedHash, $hashHeader)) {
            return true;
        }

        $signature = (string) $this->header('flutterwave-signature', '');
        $secret = (string) config('services.flutterwave.secret');

        if ($signature === '' || $secret === '') {
            return false;
        }

        $computed = hash_hmac('sha256', $this->getContent(), $secret);

        return hash_equals($computed, $signature);
    }
}
