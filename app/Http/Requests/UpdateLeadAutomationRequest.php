<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadAutomationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'workflow_name' => ['nullable', 'string', 'max:100'],
            'webhook_url' => ['nullable', 'url', 'max:500'],
            'enabled_user_ids' => ['nullable', 'array'],
            'enabled_user_ids.*' => ['uuid', 'exists:users,id'],
        ];
    }
}
