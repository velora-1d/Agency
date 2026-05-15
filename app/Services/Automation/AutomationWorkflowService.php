<?php

namespace App\Services\Automation;

use App\Models\AutomationRunLog;
use App\Models\AutomationWorkflow;
use App\Models\Workspace;
use Illuminate\Support\Facades\Schema;

class AutomationWorkflowService
{
    public function __construct(protected AutomationBlueprints $blueprints)
    {
    }

    public function create(Workspace $workspace, array $data): AutomationWorkflow
    {
        $workflow = new AutomationWorkflow();
        $workflow->workspace_id = $workspace->getKey();

        return $this->persistWorkflow($workflow, $data);
    }

    public function update(Workspace $workspace, AutomationWorkflow $workflow, array $data): AutomationWorkflow
    {
        abort_unless($workflow->workspace_id === $workspace->getKey(), 404);

        return $this->persistWorkflow($workflow, $data);
    }

    public function toggle(Workspace $workspace, AutomationWorkflow $workflow, bool $isActive): AutomationWorkflow
    {
        abort_unless($workflow->workspace_id === $workspace->getKey(), 404);

        $workflow->forceFill([
            'is_active' => $isActive,
        ])->save();

        return $workflow->refresh();
    }

    public function delete(Workspace $workspace, AutomationWorkflow $workflow): void
    {
        abort_unless($workflow->workspace_id === $workspace->getKey(), 404);

        $workflow->delete();
    }

    public function runTest(Workspace $workspace, AutomationWorkflow $workflow): AutomationRunLog
    {
        abort_unless($workflow->workspace_id === $workspace->getKey(), 404);

        $startedAt = now();
        $message = 'Uji eksekusi manual dicatat dari menu Otomasi.';
        $status = 'success';

        if ($workflow->n8n_webhook_url) {
            try {
                \Illuminate\Support\Facades\Http::post($workflow->n8n_webhook_url, [
                    'event' => 'manual_test',
                    'timestamp' => now()->toIso8601String(),
                    'data' => [
                        'message' => 'Ini adalah uji eksekusi manual dari Kantor Digital.',
                        'workspace' => $workspace->name,
                    ],
                ]);
                $message = 'Uji eksekusi berhasil dikirim ke n8n.';
            } catch (\Exception $e) {
                $status = 'failed';
                $message = 'Gagal mengirim uji eksekusi ke n8n: ' . $e->getMessage();
            }
        }

        return $this->recordRun($workflow, [
            'status' => $status,
            'trigger_event' => $workflow->trigger_event,
            'message' => $message,
            'payload' => [
                'source' => 'manual_test',
                'config' => $workflow->config,
                'test_data' => [
                    'workspace' => $workspace->name,
                ],
            ],
            'started_at' => $startedAt,
            'finished_at' => now(),
        ]);
    }

    public function recordRun(AutomationWorkflow $workflow, array $data): AutomationRunLog
    {
        if (! Schema::hasTable('automation_run_logs')) {
            return AutomationRunLog::make([
                'workspace_id' => $workflow->workspace_id,
                'automation_workflow_id' => $workflow->getKey(),
                'trigger_event' => $data['trigger_event'] ?? $workflow->trigger_event,
                'status' => $data['status'] ?? 'success',
                'attempt' => (int) ($data['attempt'] ?? 1),
                'message' => $data['message'] ?? null,
                'payload' => $data['payload'] ?? null,
                'started_at' => $data['started_at'] ?? now(),
                'finished_at' => $data['finished_at'] ?? now(),
            ]);
        }

        $startedAt = $data['started_at'] ?? now();
        $finishedAt = $data['finished_at'] ?? now();

        return AutomationRunLog::query()->create([
            'workspace_id' => $workflow->workspace_id,
            'automation_workflow_id' => $workflow->getKey(),
            'trigger_event' => $data['trigger_event'] ?? $workflow->trigger_event,
            'status' => $data['status'] ?? 'success',
            'attempt' => (int) ($data['attempt'] ?? 1),
            'message' => $data['message'] ?? null,
            'payload' => $data['payload'] ?? null,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
        ]);
    }

    protected function persistWorkflow(AutomationWorkflow $workflow, array $data): AutomationWorkflow
    {
        $template = $this->blueprints->template($data['template_key'] ?? null);
        $steps = $this->normalizeSteps($data['steps'] ?? [], $template['steps'] ?? []);
        $conditions = $this->normalizeConditions($data['conditions'] ?? [], $template['conditions'] ?? []);
        $triggerEvent = (string) ($data['trigger_event'] ?? $template['trigger_event'] ?? 'lead_created');
        $triggerType = (string) ($data['trigger_type'] ?? $template['trigger_type'] ?? 'event');

        $workflow->fill([
            'name' => $data['name'] ?: ($template['name'] ?? 'New Automation Workflow'),
            'trigger_event' => $triggerEvent,
            'n8n_workflow_id' => $data['n8n_workflow_id'] ?? null,
            'n8n_webhook_url' => $data['n8n_webhook_url'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'config' => array_filter([
                'description' => $data['description'] ?? ($template['description'] ?? null),
                'trigger_type' => $triggerType,
                'schedule_expression' => $triggerType === 'schedule'
                    ? ($data['schedule_expression'] ?? ($template['schedule_expression'] ?? null))
                    : null,
                'template_key' => $data['template_key'] ?? ($template['key'] ?? null),
                'retry_enabled' => (bool) ($data['retry_enabled'] ?? ($template['retry_enabled'] ?? false)),
                'retry_limit' => (int) ($data['retry_limit'] ?? ($template['retry_limit'] ?? 0)),
                'scope' => 'workspace',
                'conditions' => $conditions,
                'steps' => $steps,
            ], static fn (mixed $value): bool => $value !== null),
        ]);

        $workflow->save();

        return $workflow->refresh();
    }

    /**
     * @param  array<int, array<string, mixed>>  $steps
     * @param  array<int, array<string, mixed>>  $fallback
     * @return array<int, array<string, string>>
     */
    protected function normalizeSteps(array $steps, array $fallback = []): array
    {
        $normalized = collect($steps)
            ->map(function (mixed $step): array {
                $payload = is_array($step) ? $step : [];

                return [
                    'type' => trim((string) ($payload['type'] ?? '')),
                    'label' => trim((string) ($payload['label'] ?? '')),
                    'target' => trim((string) ($payload['target'] ?? '')),
                    'message' => trim((string) ($payload['message'] ?? '')),
                ];
            })
            ->filter(fn (array $step): bool => filled($step['type']) && filled($step['label']))
            ->values()
            ->all();

        if ($normalized !== []) {
            return $normalized;
        }

        return collect($fallback)
            ->map(fn (array $step): array => [
                'type' => (string) ($step['type'] ?? ''),
                'label' => (string) ($step['label'] ?? ''),
                'target' => (string) ($step['target'] ?? ''),
                'message' => (string) ($step['message'] ?? ''),
            ])
            ->filter(fn (array $step): bool => filled($step['type']) && filled($step['label']))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $conditions
     * @param  array<int, array<string, mixed>>  $fallback
     * @return array<int, array<string, string>>
     */
    protected function normalizeConditions(array $conditions, array $fallback = []): array
    {
        $normalized = collect($conditions)
            ->map(function (mixed $condition): array {
                $payload = is_array($condition) ? $condition : [];

                return [
                    'field' => trim((string) ($payload['field'] ?? '')),
                    'operator' => trim((string) ($payload['operator'] ?? 'equals')),
                    'value' => trim((string) ($payload['value'] ?? '')),
                ];
            })
            ->filter(fn (array $condition): bool => filled($condition['field']) || filled($condition['value']))
            ->values()
            ->all();

        if ($normalized !== []) {
            return $normalized;
        }

        return collect($fallback)
            ->map(fn (array $condition): array => [
                'field' => (string) ($condition['field'] ?? ''),
                'operator' => (string) ($condition['operator'] ?? 'equals'),
                'value' => (string) ($condition['value'] ?? ''),
            ])
            ->filter(fn (array $condition): bool => filled($condition['field']) || filled($condition['value']))
            ->values()
            ->all();
    }
}
