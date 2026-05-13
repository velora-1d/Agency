<?php

namespace App\Http\Requests\Automation;

use App\Services\Automation\AutomationBlueprints;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertAutomationWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $blueprints = app(AutomationBlueprints::class);

        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'trigger_event' => ['required', Rule::in(array_column($blueprints->triggerEvents(), 'value'))],
            'trigger_type' => ['required', Rule::in(array_column($blueprints->triggerTypes(), 'value'))],
            'schedule_expression' => ['nullable', 'string', 'max:255'],
            'n8n_workflow_id' => ['nullable', 'string', 'max:80'],
            'n8n_webhook_url' => ['nullable', 'url', 'max:500'],
            'template_key' => ['nullable', 'string', 'max:80'],
            'retry_enabled' => ['nullable', 'boolean'],
            'retry_limit' => ['nullable', 'integer', 'min:0', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
            'conditions' => ['nullable', 'array'],
            'conditions.*.field' => ['nullable', 'string', 'max:120'],
            'conditions.*.operator' => ['nullable', Rule::in(array_column($blueprints->conditionOperators(), 'value'))],
            'conditions.*.value' => ['nullable', 'string', 'max:255'],
            'steps' => ['nullable', 'array'],
            'steps.*.type' => ['required_with:steps', Rule::in(array_column($blueprints->stepTypes(), 'value'))],
            'steps.*.label' => ['required_with:steps', 'string', 'max:120'],
            'steps.*.target' => ['nullable', 'string', 'max:255'],
            'steps.*.message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
