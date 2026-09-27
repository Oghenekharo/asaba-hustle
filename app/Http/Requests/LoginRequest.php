<?php

namespace App\Http\Requests;

use App\Models\SiteSetting;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $phoneEnabled = SiteSetting::phoneAuthEnabled();

        return [
            'channel' => $phoneEnabled ? 'nullable|in:email,phone' : 'nullable|in:email',
            'phone' => [$phoneEnabled ? 'required_without:email' : 'prohibited', 'nullable', 'string', 'max:25'],
            'email' => [$phoneEnabled ? 'required_without:phone' : 'required', 'nullable', 'email', 'max:255'],
            'password' => 'required|string',
        ];
    }
}
