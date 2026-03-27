<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string',
            'availability_status' => 'nullable|in:available,busy,offline',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'primary_skill_id' => 'nullable|exists:skills,id',
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'integer|exists:skills,id',
            'id_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:30',
        ];
    }
}
