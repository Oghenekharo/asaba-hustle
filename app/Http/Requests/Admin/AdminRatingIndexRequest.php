<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRatingIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:255',
            'rating' => 'nullable|integer|between:1,5',
        ];
    }

    public function filters(): array
    {
        return $this->validated();
    }
}
