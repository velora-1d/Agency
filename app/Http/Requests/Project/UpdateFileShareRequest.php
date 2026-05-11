<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFileShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'share_expires_at' => ['nullable', 'date', 'after:now'],
            'regenerate' => ['nullable', 'boolean'],
        ];
    }
}
