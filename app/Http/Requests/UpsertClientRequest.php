<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:100'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'industry' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:50'],
            'province' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'on_hold'])],
            'assigned_to' => ['nullable', 'uuid', Rule::exists('users', 'id')],
            'portal_access' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
