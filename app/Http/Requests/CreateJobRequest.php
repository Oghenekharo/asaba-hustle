<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateJobRequest extends FormRequest
{
    public function after(): array
    {
        return [
            function ($validator) {
                $user = $this->user();

                if (!$user || !$user->hasRole('client')) {
                    return;
                }

                if ($user->phone_verified_at === null) {
                    $validator->errors()->add('verification', 'Verify your phone number before posting jobs.');
                }
            },
        ];
    }

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
            'skill_id' => 'required|exists:skills,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'required|numeric',
            'location' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'payment_method' => 'required|in:cash,transfer'
        ];
    }
}
