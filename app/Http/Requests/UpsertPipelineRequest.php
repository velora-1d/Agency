<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertPipelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_default' => ['boolean'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.id' => ['nullable', 'uuid'],
            'stages.*.name' => ['required', 'string', 'max:50'],
            'stages.*.color' => ['nullable', 'string', 'max:7'],
            'stages.*.is_won' => ['boolean'],
            'stages.*.is_lost' => ['boolean'],
        ];
    }
}
