<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpsertMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'uuid', 'exists:projects,id', 'required_with:action_items'],
            'client_id' => ['nullable', 'uuid', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'agenda' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'meeting_url' => ['nullable', 'url', 'max:255'],
            'recording_url' => ['nullable', 'url', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:720'],
            'status' => ['required', 'in:scheduled,completed,cancelled'],
            'internal_attendee_ids' => ['nullable', 'array'],
            'internal_attendee_ids.*' => ['nullable', 'uuid', 'exists:users,id'],
            'external_attendees' => ['nullable', 'array'],
            'external_attendees.*.name' => ['required_with:external_attendees.*.email', 'nullable', 'string', 'max:100'],
            'external_attendees.*.email' => ['nullable', 'email', 'max:255'],
            'action_items' => ['nullable', 'array'],
            'action_items.*.title' => ['required', 'string', 'max:255'],
            'action_items.*.assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
            'action_items.*.due_date' => ['nullable', 'date'],
            'action_items.*.priority' => ['nullable', 'in:low,medium,high,urgent'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasProject = filled($this->input('project_id'));
            $hasClient = filled($this->input('client_id'));

            if (! $hasProject && ! $hasClient) {
                $validator->errors()->add('project_id', 'Meeting must be linked to a project or a client.');
            }
        });
    }
}
