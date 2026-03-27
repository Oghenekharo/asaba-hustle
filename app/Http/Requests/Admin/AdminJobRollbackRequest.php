<?php

namespace App\Http\Requests\Admin;

use App\Models\ServiceJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminJobRollbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \App\Models\ServiceJob|null $job */
        $job = $this->route('job');
        $allowedTargets = $job ? ServiceJob::adminRollbackTargets($job->status) : [];

        return [
            'target_status' => ['required', 'string', Rule::in($allowedTargets)],
        ];
    }
}
