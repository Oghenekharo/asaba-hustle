<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminPaymentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:255',
            'status' => 'nullable|in:awaiting_confirmation,pending,successful,failed,refunded',
            'payment_method' => 'nullable|in:cash,transfer,paystack,flutterwave',
        ];
    }

    public function filters(): array
    {
        return $this->validated();
    }
}
