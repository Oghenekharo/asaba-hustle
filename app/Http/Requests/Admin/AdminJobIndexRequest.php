<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminJobIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:255',
            'status' => 'nullable|in:open,assigned,worker_accepted,in_progress,payment_pending,completed,rated,cancelled',
            'skill_id' => 'nullable|integer|exists:skills,id',
        ];
    }

    public function filters(): array
    {
        return $this->validated();
    }
}
