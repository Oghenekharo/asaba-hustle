<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return config('auth_methods.phone_enabled', true);
    }

    public function rules(): array
    {
        return [
            'channel' => config('auth_methods.phone_enabled', true)
                ? 'required|in:phone'
                : 'prohibited',
            'token' => 'required|string|max:20',
        ];
    }
}
