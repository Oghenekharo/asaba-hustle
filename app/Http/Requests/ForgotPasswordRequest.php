<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => 'required|in:email,phone',
            'email' => 'required_if:channel,email|nullable|email|max:255',
            'phone' => 'required_if:channel,phone|nullable|string|max:25',
        ];
    }
}
