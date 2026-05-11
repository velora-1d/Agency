<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpsertNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'folder_id' => ['nullable', 'uuid', 'exists:note_folders,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'type' => ['required', 'in:note,sop,template'],
            'is_private' => ['nullable', 'boolean'],
            'linked_task_ids' => ['nullable', 'array'],
            'linked_task_ids.*' => ['nullable', 'uuid', 'exists:tasks,id'],
        ];
    }
}
