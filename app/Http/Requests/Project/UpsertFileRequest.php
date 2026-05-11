<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpsertFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $binaryRules = ['nullable', 'file', 'max:102400'];

        if ($this->isMethod('post')) {
            array_unshift($binaryRules, 'required');
        }

        return [
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'client_id' => ['nullable', 'uuid', 'exists:clients,id'],
            'folder_id' => ['nullable', 'uuid', 'exists:file_folders,id'],
            'source_file_id' => ['nullable', 'uuid', 'exists:files,id'],
            'name' => ['required', 'string', 'max:255'],
            'binary' => $binaryRules,
            'approval_status' => ['nullable', 'in:draft,pending,approved,rejected'],
        ];
    }
}
