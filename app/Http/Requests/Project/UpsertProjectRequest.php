<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpsertProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'uuid', 'exists:clients,id'],
            'brand' => ['required', 'string', 'max:50'],
            'template_id' => ['nullable', 'uuid', 'exists:project_templates,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:planning,active,on_hold,completed'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['nullable', 'string', 'max:50'],
            'members' => ['nullable', 'array'],
            'members.*.user_id' => ['required', 'uuid', 'exists:users,id'],
            'members.*.role' => ['nullable', 'string', 'max:50'],
        ];
    }
}
