<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyJobRequest extends FormRequest
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
            'message' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $user = $this->user();

                if (!$user || !$user->hasRole('worker')) {
                    return;
                }

                $missingFields = collect([
                    'bank name' => $user->bank_name,
                    'account name' => $user->account_name,
                    'account number' => $user->account_number,
                ])->filter(fn($value) => !filled($value))->keys()->values();

                if ($missingFields->isEmpty()) {
                    return;
                }

                $validator->errors()->add(
                    'account_details',
                    'Update your ' . $missingFields->join(', ') . ' in your profile before applying for jobs.'
                );
            },
        ];
    }
}
