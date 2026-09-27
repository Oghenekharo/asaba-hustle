<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendVerificationTokenRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->filled('channel')) {
            $this->merge([
                'channel' => config('auth_methods.phone_enabled', true) ? 'phone' : 'email',
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => config('auth_methods.phone_enabled', true)
                ? 'required|in:email,phone'
                : 'required|in:email',
        ];
    }
}
