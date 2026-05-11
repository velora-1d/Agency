<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pipeline_id' => ['nullable', 'uuid', Rule::exists('pipelines', 'id')],
            'stage_id' => ['nullable', 'uuid', Rule::exists('pipeline_stages', 'id')],
            'name' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:50'],
            'province' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:50'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['required', 'string', Rule::in(['low', 'medium', 'high'])],
            'assigned_to' => ['nullable', 'uuid', Rule::exists('users', 'id')],
            'notes' => ['nullable', 'string'],
        ];
    }
}
