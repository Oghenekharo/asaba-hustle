<?php

namespace App\Http\Requests;

use App\Models\SiteSetting;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $phoneEnabled = SiteSetting::phoneAuthEnabled();

        return [
            'channel' => $phoneEnabled ? 'required|in:email,phone' : 'required|in:email',
            'email' => [$phoneEnabled ? 'required_if:channel,email' : 'required', 'nullable', 'email', 'max:255'],
            'phone' => [$phoneEnabled ? 'required_if:channel,phone' : 'prohibited', 'nullable', 'string', 'max:25'],
        ];
    }
}
