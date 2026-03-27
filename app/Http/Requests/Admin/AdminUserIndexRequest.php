<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,suspended,banned',
            'role' => 'nullable|in:admin,client,worker',
        ];
    }

    public function filters(): array
    {
        return $this->validated();
    }
}
