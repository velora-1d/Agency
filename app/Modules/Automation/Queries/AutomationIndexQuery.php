<?php

namespace App\Modules\Automation\Queries;

use App\Models\AutomationRunLog;
use App\Models\AutomationWorkflow;
use App\Models\Workspace;
use App\Services\Automation\AutomationBlueprints;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class AutomationIndexQuery
{
    public function __construct(protected AutomationBlueprints $blueprints)
    {
    }

    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $workflows = $this->workflowQuery($workspace, $filters)->get();
        $logs = $this->resolveLogs($workspace, $workflows);
        $selectedWorkflowId = $this->resolveSelectedWorkflowId($filters['workflow'], $workflows);

        return [
            'automation' => [
                'summary' => $this->buildSummary($workflows, $logs),
                'selected_id' => $selectedWorkflowId,
                'items' => $workflows
                    ->map(fn (AutomationWorkflow $workflow): array => $this->transformWorkflow(
                        $workflow,
                        $logs->where('automation_workflow_id', $workflow->getKey())->values()
                    ))
                    ->values()
                    ->all(),
                'logs' => $logs
                    ->take(12)
                    ->map(fn (AutomationRunLog $log): array => $this->transformLog($log))
                    ->values()
                    ->all(),
                'templates' => $this->transformTemplates(),
                'options' => [
                    'statuses' => $this->blueprints->statusOptions(),
                    'trigger_events' => $this->blueprints->triggerEvents(),
                    'trigger_types' => $this->blueprints->triggerTypes(),
                    'step_types' => $this->blueprints->stepTypes(),
                    'operators' => $this->blueprints->conditionOperators(),
                ],
            ],
            'filters' => $filters,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'search' => $normalize($input['search'] ?? null),
            'status' => $normalize($input['status'] ?? null),
            'trigger_type' => $normalize($input['trigger_type'] ?? null),
            'template' => $normalize($input['template'] ?? null),
            'workflow' => $normalize($input['workflow'] ?? null),
        ];
    }

    /**
     * @param  array<string, string|null>  $filters
     */
    protected function workflowQuery(Workspace $workspace, array $filters): Builder
    {
        return AutomationWorkflow::query()
            ->where('workspace_id', $workspace->getKey())
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('trigger_event', 'like', "%{$search}%")
                        ->orWhere('n8n_workflow_id', 'like', "%{$search}%")
                        ->orWhere('n8n_webhook_url', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn (Builder $query) => $query->where('is_active', false))
            ->when($filters['trigger_type'], fn (Builder $query, string $triggerType) => $query->where('config->trigger_type', $triggerType))
            ->when($filters['template'], fn (Builder $query, string $template) => $query->where('config->template_key', $template))
            ->orderByDesc('is_active')
            ->orderBy('name');
    }

    protected function resolveLogs(Workspace $workspace, Collection $workflows): Collection
    {
        if (! Schema::hasTable('automation_run_logs')) {
            return collect();
        }

        return $this->runLogQuery($workspace, $workflows)->get();
    }

    protected function runLogQuery(Workspace $workspace, Collection $workflows): Builder
    {
        $query = AutomationRunLog::query()
            ->where('workspace_id', $workspace->getKey())
            ->with('workflow:id,name,trigger_event')
            ->latest('started_at');

        if ($workflows->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('automation_workflow_id', $workflows->pluck('id')->all());
    }

    protected function resolveSelectedWorkflowId(?string $requestedWorkflowId, Collection $workflows): ?string
    {
        if ($requestedWorkflowId && $workflows->contains(fn (AutomationWorkflow $workflow): bool => $workflow->getKey() === $requestedWorkflowId)) {
            return $requestedWorkflowId;
        }

        return $workflows->first()?->getKey();
    }

    protected function buildSummary(Collection $workflows, Collection $logs): array
    {
        $totalRuns = $logs->count();
        $successRuns = $logs->where('status', 'success')->count();
        $failedRuns = $logs->where('status', 'failed')->count();

        return [
            'total_workflows' => $workflows->count(),
            'active_workflows' => $workflows->where('is_active', true)->count(),
            'scheduled_workflows' => $workflows->filter(
                fn (AutomationWorkflow $workflow): bool => ($workflow->config['trigger_type'] ?? 'event') === 'schedule'
            )->count(),
            'n8n_linked_workflows' => $workflows->filter(
                fn (AutomationWorkflow $workflow): bool => filled($workflow->n8n_webhook_url) || filled($workflow->n8n_workflow_id)
            )->count(),
            'total_runs' => $totalRuns,
            'success_runs' => $successRuns,
            'failed_runs' => $failedRuns,
            'success_rate' => $totalRuns > 0 ? (int) round(($successRuns / $totalRuns) * 100) : 0,
        ];
    }

    protected function transformWorkflow(AutomationWorkflow $workflow, Collection $logs): array
    {
        $config = $workflow->config ?? [];
        $latestLog = $logs->sortByDesc('started_at')->first();

        return [
            'id' => $workflow->getKey(),
            'name' => $workflow->name,
            'trigger_event' => $workflow->trigger_event,
            'trigger_label' => $this->labelForOption($workflow->trigger_event, $this->blueprints->triggerEvents()),
            'n8n_workflow_id' => $workflow->n8n_workflow_id,
            'n8n_webhook_url' => $workflow->n8n_webhook_url,
            'is_active' => (bool) $workflow->is_active,
            'config' => [
                'description' => $config['description'] ?? null,
                'trigger_type' => $config['trigger_type'] ?? 'event',
                'schedule_expression' => $config['schedule_expression'] ?? null,
                'template_key' => $config['template_key'] ?? null,
                'retry_enabled' => (bool) ($config['retry_enabled'] ?? false),
                'retry_limit' => (int) ($config['retry_limit'] ?? 0),
                'scope' => $config['scope'] ?? 'workspace',
                'conditions' => array_values($config['conditions'] ?? []),
                'steps' => array_values($config['steps'] ?? []),
            ],
            'counts' => [
                'steps' => count($config['steps'] ?? []),
                'conditions' => count($config['conditions'] ?? []),
                'runs' => $logs->count(),
                'success_runs' => $logs->where('status', 'success')->count(),
                'failed_runs' => $logs->where('status', 'failed')->count(),
            ],
            'last_run' => $latestLog ? [
                'status' => $latestLog->status,
                'status_label' => $this->statusLabel($latestLog->status),
                'message' => $latestLog->message,
                'started_at' => $latestLog->started_at?->toIso8601String(),
                'started_at_label' => $latestLog->started_at?->diffForHumans(),
            ] : null,
            'created_at' => $workflow->created_at?->toIso8601String(),
            'created_at_label' => $workflow->created_at?->diffForHumans(),
        ];
    }

    protected function transformLog(AutomationRunLog $log): array
    {
        return [
            'id' => $log->getKey(),
            'workflow_id' => $log->automation_workflow_id,
            'workflow_name' => $log->workflow?->name,
            'trigger_event' => $log->trigger_event,
            'trigger_label' => $this->labelForOption($log->trigger_event, $this->blueprints->triggerEvents()),
            'status' => $log->status,
            'status_label' => $this->statusLabel($log->status),
            'attempt' => $log->attempt,
            'message' => $log->message,
            'started_at' => $log->started_at?->toIso8601String(),
            'started_at_label' => $log->started_at?->format('d M Y H:i'),
            'started_at_human' => $log->started_at?->diffForHumans(),
            'finished_at' => $log->finished_at?->toIso8601String(),
            'payload' => $log->payload,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function transformTemplates(): array
    {
        return collect($this->blueprints->templates())
            ->map(fn (array $template): array => [
                'key' => $template['key'],
                'name' => $template['name'],
                'description' => $template['description'],
                'trigger_event' => $template['trigger_event'],
                'trigger_label' => $this->labelForOption($template['trigger_event'], $this->blueprints->triggerEvents()),
                'trigger_type' => $template['trigger_type'],
                'retry_enabled' => (bool) $template['retry_enabled'],
                'retry_limit' => (int) $template['retry_limit'],
                'steps' => $template['steps'],
                'conditions' => $template['conditions'],
                'step_count' => count($template['steps']),
                'condition_count' => count($template['conditions']),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{value: string, label: string}>  $options
     */
    protected function labelForOption(string $value, array $options): string
    {
        $match = collect($options)->firstWhere('value', $value);

        return $match['label'] ?? ucfirst(str_replace('_', ' ', $value));
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'success' => 'Berhasil',
            'failed' => 'Gagal',
            'skipped' => 'Dilewati',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
