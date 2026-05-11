<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpsertTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'parent_task_id' => ['nullable', 'uuid', 'exists:tasks,id'],
            'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:todo,in_progress,review,done'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['nullable', 'string', 'max:50'],
            'due_date' => ['nullable', 'date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_rule' => ['nullable', 'in:daily,weekly,monthly'],
            'template_id' => ['nullable', 'uuid', 'exists:task_templates,id'],
            'sop_note_id' => ['nullable', 'uuid', 'exists:notes,id'],
            'dependency_ids' => ['nullable', 'array'],
            'dependency_ids.*' => ['nullable', 'uuid', 'exists:tasks,id'],
        ];
    }
}
