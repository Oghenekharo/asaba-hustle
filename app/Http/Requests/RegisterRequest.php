<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email|required_if:verification_method,email',
            'password' => 'required|string|min:6|confirmed',
            'primary_skill_id' => 'nullable|exists:skills,id',
            'role' => 'required|in:client,worker',
            'verification_method' => 'required|in:email,phone',
        ];
    }
}
