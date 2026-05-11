<?php

namespace App\Modules\CRM\Leads\Queries;

use App\Models\AutomationWorkflow;
use App\Models\Form;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LeadIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $pipelines = $this->pipelineQuery($workspace)->get();
        $leads = $this->leadQuery($workspace, $filters)->get();

        return [
            'crm' => [
                'summary' => $this->buildSummary($workspace, $leads, $pipelines),
                'automation' => $this->buildAutomationPayload($workspace),
                'pipelines' => $this->buildPipelinePayload($pipelines, $leads),
                'table' => $leads
                    ->map(fn (Lead $lead): array => $this->transformLead($lead))
                    ->values()
                    ->all(),
            ],
            'filters' => $filters,
            'filterOptions' => $this->buildFilterOptions($workspace, $pipelines),
        ];
    }

    public function getExportRows(Workspace $workspace, array $input = []): Collection
    {
        $filters = $this->normalizeFilters($input);

        return $this->leadQuery($workspace, $filters)
            ->get()
            ->map(function (Lead $lead): array {
                return [
                    'Name' => $lead->name,
                    'Company' => $lead->company ?? '',
                    'Email' => $lead->email ?? '',
                    'Phone' => $lead->phone ?? '',
                    'Source' => $lead->source ?? '',
                    'Pipeline' => $lead->pipeline?->name ?? '',
                    'Stage' => $lead->stage?->name ?? 'Unassigned',
                    'Priority' => $lead->priority,
                    'AI Score' => $lead->ai_score ?? '',
                    'Estimated Value' => $lead->estimated_value ? number_format((float) $lead->estimated_value, 2, '.', '') : '',
                    'Assignee' => $lead->assignee?->name ?? '',
                    'Created At' => optional($lead->created_at)?->toDateTimeString() ?? '',
                    'Notes' => $lead->notes ?? '',
                ];
            });
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string|null>
     */
    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'pipeline' => $normalize($input['pipeline'] ?? null),
            'stage' => $normalize($input['stage'] ?? null),
            'source' => $normalize($input['source'] ?? null),
            'assignee' => $normalize($input['assignee'] ?? null),
            'date' => $normalize($input['date'] ?? null),
            'min_value' => $normalize($input['min_value'] ?? null),
            'max_value' => $normalize($input['max_value'] ?? null),
        ];
    }

    protected function pipelineQuery(Workspace $workspace): Builder
    {
        return Pipeline::query()
            ->where('workspace_id', $workspace->getKey())
            ->with(['stages'])
            ->orderByDesc('is_default')
            ->orderBy('name');
    }

    /**
     * @param  array<string, string|null>  $filters
     */
    protected function leadQuery(Workspace $workspace, array $filters): Builder
    {
        $query = Lead::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'pipeline:id,name',
                'stage:id,pipeline_id,name,color,is_won,is_lost',
                'assignee:id,name',
            ])
            ->orderByDesc('created_at');

        $this->applyFilters($query, $filters);

        return $query;
    }

    /**
     * @param  array<string, string|null>  $filters
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['pipeline'], fn (Builder $builder, string $pipeline) => $builder->where('pipeline_id', $pipeline))
            ->when($filters['stage'], fn (Builder $builder, string $stage) => $builder->where('stage_id', $stage))
            ->when($filters['source'], fn (Builder $builder, string $source) => $builder->where('source', $source))
            ->when($filters['assignee'], fn (Builder $builder, string $assignee) => $builder->where('assigned_to', $assignee))
            ->when($filters['min_value'], fn (Builder $builder, string $minValue) => $builder->where('estimated_value', '>=', (float) $minValue))
            ->when($filters['max_value'], fn (Builder $builder, string $maxValue) => $builder->where('estimated_value', '<=', (float) $maxValue));

        match ($filters['date']) {
            '7d' => $query->where('created_at', '>=', now()->subDays(7)),
            '30d' => $query->where('created_at', '>=', now()->subDays(30)),
            '90d' => $query->where('created_at', '>=', now()->subDays(90)),
            'this_month' => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            default => null,
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildPipelinePayload(Collection $pipelines, Collection $leads): array
    {
        $payload = $pipelines
            ->map(function (Pipeline $pipeline) use ($leads): array {
                $pipelineLeads = $leads->where('pipeline_id', $pipeline->getKey())->values();

                $stages = $pipeline->stages
                    ->map(function (PipelineStage $stage) use ($pipelineLeads): array {
                        $stageLeads = $pipelineLeads
                            ->where('stage_id', $stage->getKey())
                            ->map(fn (Lead $lead): array => $this->transformLead($lead))
                            ->values()
                            ->all();

                        return [
                            'id' => $stage->getKey(),
                            'name' => $stage->name,
                            'color' => $stage->color,
                            'is_won' => $stage->is_won,
                            'is_lost' => $stage->is_lost,
                            'lead_count' => count($stageLeads),
                            'total_value' => $this->sumLeadValue(collect($stageLeads)),
                            'leads' => $stageLeads,
                        ];
                    })
                    ->values();

                $unassignedLeads = $pipelineLeads
                    ->whereNull('stage_id')
                    ->map(fn (Lead $lead): array => $this->transformLead($lead))
                    ->values()
                    ->all();

                if ($unassignedLeads !== []) {
                    $stages->push([
                        'id' => 'unassigned-' . $pipeline->getKey(),
                        'name' => 'Unassigned',
                        'color' => '#A8A29E',
                        'is_won' => false,
                        'is_lost' => false,
                        'lead_count' => count($unassignedLeads),
                        'total_value' => $this->sumLeadValue(collect($unassignedLeads)),
                        'leads' => $unassignedLeads,
                    ]);
                }

                return [
                    'id' => $pipeline->getKey(),
                    'name' => $pipeline->name,
                    'description' => $pipeline->description,
                    'is_default' => $pipeline->is_default,
                    'lead_count' => $pipelineLeads->count(),
                    'stages' => $stages->all(),
                ];
            })
            ->values();

        $withoutPipeline = $leads
            ->whereNull('pipeline_id')
            ->map(fn (Lead $lead): array => $this->transformLead($lead))
            ->values()
            ->all();

        if ($withoutPipeline !== []) {
            $payload->push([
                'id' => 'no-pipeline',
                'name' => 'No Pipeline',
                'description' => 'Lead belum punya pipeline.',
                'is_default' => false,
                'lead_count' => count($withoutPipeline),
                'stages' => [[
                    'id' => 'unassigned-no-pipeline',
                    'name' => 'Unassigned',
                    'color' => '#A8A29E',
                    'is_won' => false,
                    'is_lost' => false,
                    'lead_count' => count($withoutPipeline),
                    'total_value' => $this->sumLeadValue(collect($withoutPipeline)),
                    'leads' => $withoutPipeline,
                ]],
            ]);
        }

        return $payload->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSummary(Workspace $workspace, Collection $leads, Collection $pipelines): array
    {
        $scores = $leads->pluck('ai_score')->filter(fn (mixed $score): bool => is_numeric($score));

        return [
            'total_leads' => $leads->count(),
            'pipeline_count' => $pipelines->count(),
            'open_value' => $this->sumLeadValue(
                $leads->map(fn (Lead $lead): array => ['estimated_value_raw' => (float) ($lead->estimated_value ?? 0)])
            ),
            'high_priority' => $leads->where('priority', 'high')->count(),
            'avg_ai_score' => $scores->isEmpty() ? null : (int) round($scores->avg()),
            'forms_auto_create_enabled' => Form::query()
                ->where('workspace_id', $workspace->getKey())
                ->where('auto_create_lead', true)
                ->count(),
            'auto_whatsapp_enabled' => AutomationWorkflow::query()
                ->where('workspace_id', $workspace->getKey())
                ->where('trigger_event', 'lead_created')
                ->where('is_active', true)
                ->exists(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilterOptions(Workspace $workspace, Collection $pipelines): array
    {
        $sources = Lead::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->distinct()
            ->orderBy('source')
            ->pluck('source')
            ->values()
            ->all();

        $assignees = $workspace->users()
            ->select('users.id', 'users.name')
            ->orderBy('users.name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->getKey(),
                'name' => $user->name,
            ])
            ->values()
            ->all();

        return [
            'pipelines' => $pipelines
                ->map(fn (Pipeline $pipeline): array => [
                    'id' => $pipeline->getKey(),
                    'name' => $pipeline->name,
                ])
                ->values()
                ->all(),
            'stages' => $pipelines
                ->flatMap(fn (Pipeline $pipeline) => $pipeline->stages)
                ->map(fn (PipelineStage $stage): array => [
                    'id' => $stage->getKey(),
                    'pipeline_id' => $stage->pipeline_id,
                    'name' => $stage->name,
                ])
                ->values()
                ->all(),
            'sources' => $sources,
            'assignees' => $assignees,
            'date_ranges' => [
                ['value' => '7d', 'label' => 'Last 7 days'],
                ['value' => '30d', 'label' => 'Last 30 days'],
                ['value' => '90d', 'label' => 'Last 90 days'],
                ['value' => 'this_month', 'label' => 'This month'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildAutomationPayload(Workspace $workspace): array
    {
        $workflow = AutomationWorkflow::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('trigger_event', 'lead_created')
            ->latest('created_at')
            ->first();

        return [
            'enabled' => (bool) $workflow?->is_active,
            'workflow_name' => $workflow?->name ?? 'Lead WhatsApp Follow Up',
            'webhook_url' => $workflow?->n8n_webhook_url ?? $workspace->n8n_webhook_url,
            'enabled_user_ids' => $workflow?->config['enabled_user_ids'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function transformLead(Lead $lead): array
    {
        $value = (float) ($lead->estimated_value ?? 0);

        return [
            'id' => $lead->getKey(),
            'name' => $lead->name,
            'company' => $lead->company,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'source' => $lead->source,
            'priority' => $lead->priority,
            'ai_score' => $lead->ai_score,
            'notes' => $lead->notes,
            'estimated_value_raw' => $value,
            'estimated_value_label' => $value > 0 ? 'Rp ' . number_format($value, 0, ',', '.') : 'Belum diisi',
            'created_at' => optional($lead->created_at)?->toIso8601String(),
            'created_at_label' => optional($lead->created_at)?->diffForHumans(),
            'assignee' => $lead->assignee ? [
                'id' => $lead->assignee->getKey(),
                'name' => $lead->assignee->name,
            ] : null,
            'pipeline' => $lead->pipeline ? [
                'id' => $lead->pipeline->getKey(),
                'name' => $lead->pipeline->name,
            ] : null,
            'stage' => $lead->stage ? [
                'id' => $lead->stage->getKey(),
                'name' => $lead->stage->name,
                'color' => $lead->stage->color,
                'is_won' => $lead->stage->is_won,
                'is_lost' => $lead->stage->is_lost,
            ] : null,
        ];
    }

    protected function sumLeadValue(Collection $leads): string
    {
        $sum = $leads->sum(fn (array $lead): float => (float) ($lead['estimated_value_raw'] ?? 0));

        return 'Rp ' . number_format($sum, 0, ',', '.');
    }
}
