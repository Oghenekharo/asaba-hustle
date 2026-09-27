<?php

namespace App\Http\Requests;

use App\Models\SiteSetting;
use Illuminate\Foundation\Http\FormRequest;

class VerifyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return SiteSetting::phoneAuthEnabled();
    }

    public function rules(): array
    {
        return [
            'channel' => SiteSetting::phoneAuthEnabled()
                ? 'required|in:phone'
                : 'prohibited',
            'token' => 'required|string|max:20',
        ];
    }
}
