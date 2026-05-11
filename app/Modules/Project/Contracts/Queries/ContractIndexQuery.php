<?php

namespace App\Modules\Project\Contracts\Queries;

use App\Models\ActivityFeed;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ContractIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $contracts = $this->contractQuery($workspace, $filters)->get();

        return [
            'contracts' => [
                'summary' => $this->buildSummary($contracts),
                'table' => $contracts->map(fn (Contract $contract): array => $this->transformContract($contract, $workspace))->values()->all(),
            ],
            'filters' => $filters,
            'filterOptions' => $this->buildFilterOptions($workspace),
        ];
    }

    public function getShowPayload(Workspace $workspace, Contract $contract): array
    {
        abort_unless($contract->workspace_id === $workspace->getKey(), 404);

        $contract->load([
            'client:id,company_name',
            'project:id,name',
            'creator:id,name',
        ]);

        $activities = ActivityFeed::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('subject_type', Contract::class)
            ->where('subject_id', $contract->getKey())
            ->with(['user:id,name', 'comments.user:id,name'])
            ->latest('created_at')
            ->get();

        return [
            'contract' => $this->transformContract($contract, $workspace) + [
                'content' => $contract->content,
                'file_url' => $contract->file_path ? \Illuminate\Support\Facades\Storage::url($contract->file_path) : null,
                'signed_file_url' => $contract->signed_file_path ? \Illuminate\Support\Facades\Storage::url($contract->signed_file_path) : null,
                'signed_at' => $contract->signed_at?->toIso8601String(),
                'signed_at_label' => $contract->signed_at?->format('d M Y H:i'),
                'signed_by_name' => $contract->signed_by_name,
                'esign_url' => route('contracts.public.esign', ['token' => $contract->esign_token]),
                'reminder_days_before' => $contract->reminder_days_before,
                'client' => $contract->client ? [
                    'id' => $contract->client->id,
                    'name' => $contract->client->company_name,
                ] : null,
                'project' => $contract->project ? [
                    'id' => $contract->project->id,
                    'name' => $contract->project->name,
                ] : null,
            ],
            'activities' => \App\Http\Resources\LeadActivityResource::collection($activities)->resolve(),
        ];
    }

    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'status' => $normalize($input['status'] ?? null),
            'client_id' => $normalize($input['client_id'] ?? null),
            'project_id' => $normalize($input['project_id'] ?? null),
            'date' => $normalize($input['date'] ?? null),
        ];
    }

    protected function contractQuery(Workspace $workspace, array $filters): Builder
    {
        $query = Contract::query()
            ->where('workspace_id', $workspace->getKey())
            ->with(['client:id,company_name', 'project:id,name'])
            ->latest('created_at');

        $query
            ->when($filters['status'], fn (Builder $builder, string $status) => $builder->where('status', $status))
            ->when($filters['client_id'], fn (Builder $builder, string $clientId) => $builder->where('client_id', $clientId))
            ->when($filters['project_id'], fn (Builder $builder, string $projectId) => $builder->where('project_id', $projectId));

        match ($filters['date']) {
            '7d' => $query->where('created_at', '>=', now()->subDays(7)),
            '30d' => $query->where('created_at', '>=', now()->subDays(30)),
            'expired' => $query->where('end_date', '<', now())->where('status', '!=', 'signed'),
            default => null,
        };

        return $query;
    }

    protected function buildSummary(Collection $contracts): array
    {
        return [
            'total_contracts' => $contracts->count(),
            'draft_contracts' => $contracts->where('status', 'draft')->count(),
            'sent_contracts' => $contracts->where('status', 'sent')->count(),
            'signed_contracts' => $contracts->where('status', 'signed')->count(),
            'total_value' => $contracts->sum('value'),
        ];
    }

    protected function buildFilterOptions(Workspace $workspace): array
    {
        $clients = Client::query()
            ->where('workspace_id', $workspace->getKey())
            ->orderBy('company_name')
            ->get(['id', 'company_name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->company_name])
            ->all();

        $projects = Project::query()
            ->where('workspace_id', $workspace->getKey())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();

        return [
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'sent', 'label' => 'Sent'],
                ['value' => 'signed', 'label' => 'Signed'],
                ['value' => 'expired', 'label' => 'Expired'],
            ],
            'clients' => $clients,
            'projects' => $projects,
            'templates' => ContractTemplate::query()->where('workspace_id', $workspace->getKey())->get(['id', 'name', 'content'])->all(),
        ];
    }

    public function transformContract(Contract $contract, Workspace $workspace): array
    {
        return [
            'id' => $contract->getKey(),
            'title' => $contract->title,
            'status' => $contract->status,
            'value' => (float) ($contract->value ?? 0),
            'value_label' => $this->formatCurrency($workspace, (float) ($contract->value ?? 0)),
            'start_date' => $contract->start_date,
            'start_date_label' => $contract->start_date ? \Illuminate\Support\Carbon::parse($contract->start_date)->format('d M Y') : null,
            'end_date' => $contract->end_date,
            'end_date_label' => $contract->end_date ? \Illuminate\Support\Carbon::parse($contract->end_date)->format('d M Y') : null,
            'created_at_label' => $contract->created_at?->diffForHumans(),
            'client_name' => $contract->client?->company_name ?? 'No Client',
            'project_name' => $contract->project?->name ?? 'No Project',
            'has_signed_document' => filled($contract->signed_file_path),
        ];
    }

    protected function formatCurrency(Workspace $workspace, float $value): string
    {
        $prefix = strtoupper((string) $workspace->currency) === 'IDR' ? 'Rp ' : strtoupper((string) $workspace->currency) . ' ';
        return $prefix . number_format($value, 0, ',', '.');
    }
}
