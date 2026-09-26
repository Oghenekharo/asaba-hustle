<?php

namespace App\Http\Requests;

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
        return [
            'channel' => 'nullable|in:email,phone',
            'phone' => 'required_if:channel,phone|required_without:email|nullable|string|max:25',
            'email' => 'required_if:channel,email|required_without:phone|nullable|email|max:255',
            'password' => 'required|string',
        ];
    }
}
