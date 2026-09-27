<?php

namespace App\Http\Requests;

use App\Models\SiteSetting;
use Illuminate\Foundation\Http\FormRequest;

class SendVerificationTokenRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->filled('channel')) {
            $this->merge([
                'channel' => SiteSetting::phoneAuthEnabled() ? 'phone' : 'email',
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
            'channel' => SiteSetting::phoneAuthEnabled()
                ? 'required|in:email,phone'
                : 'required|in:email',
        ];
    }
}
