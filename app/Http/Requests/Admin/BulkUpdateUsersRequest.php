<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:active,suspended,banned',
            'users' => 'required|array|min:1',
            'users.*' => 'integer|exists:users,id',
        ];
    }
}
