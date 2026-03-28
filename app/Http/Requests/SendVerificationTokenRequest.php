<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendVerificationTokenRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->filled('channel')) {
            $this->merge([
                'channel' => 'phone',
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
            'channel' => 'required|in:email,phone',
        ];
    }
}
