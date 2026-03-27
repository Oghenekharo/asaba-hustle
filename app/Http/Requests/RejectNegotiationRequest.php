<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectNegotiationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'message' => ['required', 'string', 'max:1000'],
        ];
    }
}
