<?php

namespace App\Modules\CRM\Clients\Queries;

use App\Models\ActivityFeed;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Server;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Website;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ClientIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $clients = $this->clientQuery($workspace, $filters)->get();

        return [
            'clients' => [
                'summary' => $this->buildSummary($clients),
                'table' => $clients->map(fn (Client $client): array => $this->transformClient($client))->values()->all(),
            ],
            'filters' => $filters,
            'filterOptions' => $this->buildFilterOptions($workspace),
        ];
    }

    public function getShowPayload(Workspace $workspace, Client $client): array
    {
        abort_unless($client->workspace_id === $workspace->getKey(), 404);

        $client->load([
            'assignedUser:id,name',
            'lead:id,name,company',
            'projects' => fn ($query) => $query->latest('created_at'),
            'invoices' => fn ($query) => $query->latest('created_at'),
            'contracts' => fn ($query) => $query->latest('created_at'),
            'supportTickets.assignee:id,name',
            'websites.domain',
            'websites.server',
        ])->loadCount(['projects', 'invoices', 'contracts', 'supportTickets']);

        $activities = ActivityFeed::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('subject_type', Client::class)
            ->where('subject_id', $client->getKey())
            ->with(['user:id,name', 'comments.user:id,name'])
            ->latest('created_at')
            ->get();

        return [
            'client' => $this->transformClientDetail($client, $workspace),
            'tabs' => [
                'projects' => $client->projects->map(fn (Project $project): array => $this->transformProject($project, $workspace))->values()->all(),
                'invoices' => $client->invoices->map(fn (Invoice $invoice): array => $this->transformInvoice($invoice, $workspace))->values()->all(),
                'contracts' => $client->contracts->map(fn (Contract $contract): array => $this->transformContract($contract, $workspace))->values()->all(),
                'tickets' => $client->supportTickets->map(fn (SupportTicket $ticket): array => $this->transformTicket($ticket))->values()->all(),
                'digital_services' => $client->websites->map(fn (Website $website): array => $this->transformWebsite($website))->values()->all(),
            ],
            'activities' => \App\Http\Resources\LeadActivityResource::collection($activities)->resolve(),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string|null>
     */
    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'status' => $normalize($input['status'] ?? null),
            'industry' => $normalize($input['industry'] ?? null),
            'assignee' => $normalize($input['assignee'] ?? null),
            'date' => $normalize($input['date'] ?? null),
        ];
    }

    /**
     * @param  array<string, string|null>  $filters
     */
    protected function clientQuery(Workspace $workspace, array $filters): Builder
    {
        $query = Client::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'assignedUser:id,name',
                'lead:id,name,company',
            ])
            ->withCount(['projects', 'invoices', 'contracts', 'supportTickets'])
            ->latest('created_at');

        $query
            ->when($filters['status'], fn (Builder $builder, string $status) => $builder->where('status', $status))
            ->when($filters['industry'], fn (Builder $builder, string $industry) => $builder->where('industry', $industry))
            ->when($filters['assignee'], fn (Builder $builder, string $assignee) => $builder->where('assigned_to', $assignee));

        match ($filters['date']) {
            '7d' => $query->where('created_at', '>=', now()->subDays(7)),
            '30d' => $query->where('created_at', '>=', now()->subDays(30)),
            '90d' => $query->where('created_at', '>=', now()->subDays(90)),
            'this_month' => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            default => null,
        };

        return $query;
    }

    protected function buildSummary(Collection $clients): array
    {
        return [
            'total_clients' => $clients->count(),
            'active_clients' => $clients->where('status', 'active')->count(),
            'on_hold_clients' => $clients->where('status', 'on_hold')->count(),
            'portal_enabled' => $clients->where('portal_access', true)->count(),
        ];
    }

    protected function buildFilterOptions(Workspace $workspace): array
    {
        $industries = Client::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereNotNull('industry')
            ->where('industry', '!=', '')
            ->distinct()
            ->orderBy('industry')
            ->pluck('industry')
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
            'statuses' => [
                ['value' => 'active', 'label' => 'Aktif'],
                ['value' => 'inactive', 'label' => 'Nonaktif'],
                ['value' => 'on_hold', 'label' => 'Ditahan'],
            ],
            'industries' => $industries,
            'assignees' => $assignees,
            'date_ranges' => [
                ['value' => '7d', 'label' => '7 Hari Terakhir'],
                ['value' => '30d', 'label' => '30 Hari Terakhir'],
                ['value' => '90d', 'label' => '90 Hari Terakhir'],
                ['value' => 'this_month', 'label' => 'Bulan Ini'],
            ],
        ];
    }

    public function transformClient(Client $client): array
    {
        return [
            'id' => $client->getKey(),
            'company_name' => $client->company_name,
            'pic_name' => $client->pic_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'industry' => $client->industry,
            'address' => $client->address,
            'city' => $client->city,
            'province' => $client->province,
            'status' => $client->status,
            'portal_access' => (bool) $client->portal_access,
            'notes' => $client->notes,
            'created_at' => $client->created_at?->toIso8601String(),
            'created_at_label' => $client->created_at?->diffForHumans(),
            'location' => collect([$client->city, $client->province])->filter()->implode(', '),
            'assigned_user' => $client->assignedUser ? [
                'id' => $client->assignedUser->getKey(),
                'name' => $client->assignedUser->name,
            ] : null,
            'lead' => $client->lead ? [
                'id' => $client->lead->getKey(),
                'name' => $client->lead->name,
                'company' => $client->lead->company,
            ] : null,
            'counts' => [
                'projects' => $client->projects_count ?? 0,
                'invoices' => $client->invoices_count ?? 0,
                'contracts' => $client->contracts_count ?? 0,
                'tickets' => $client->support_tickets_count ?? 0,
            ],
        ];
    }

    protected function transformClientDetail(Client $client, Workspace $workspace): array
    {
        return $this->transformClient($client) + [
            'portal_token' => $client->portal_token,
            'currency' => $workspace->currency,
        ];
    }

    protected function transformProject(Project $project, Workspace $workspace): array
    {
        return [
            'id' => $project->getKey(),
            'name' => $project->name,
            'status' => $project->status,
            'progress' => $project->progress,
            'budget_label' => $this->formatCurrency($workspace, (float) ($project->budget ?? 0)),
            'start_date_label' => $project->start_date?->format('d M Y'),
            'end_date_label' => $project->end_date?->format('d M Y'),
        ];
    }

    protected function transformInvoice(Invoice $invoice, Workspace $workspace): array
    {
        return [
            'id' => $invoice->getKey(),
            'number' => $invoice->number,
            'type' => $invoice->type,
            'status' => $invoice->status,
            'total_label' => $this->formatCurrency($workspace, (float) ($invoice->total ?? 0)),
            'paid_amount_label' => $this->formatCurrency($workspace, (float) ($invoice->paid_amount ?? 0)),
            'due_date_label' => $invoice->due_date?->format('d M Y'),
        ];
    }

    protected function transformContract(Contract $contract, Workspace $workspace): array
    {
        return [
            'id' => $contract->getKey(),
            'title' => $contract->title,
            'status' => $contract->status,
            'value_label' => $this->formatCurrency($workspace, (float) ($contract->value ?? 0)),
            'start_date_label' => $contract->start_date ? \Illuminate\Support\Carbon::parse($contract->start_date)->format('d M Y') : null,
            'end_date_label' => $contract->end_date ? \Illuminate\Support\Carbon::parse($contract->end_date)->format('d M Y') : null,
        ];
    }

    protected function transformTicket(SupportTicket $ticket): array
    {
        return [
            'id' => $ticket->getKey(),
            'title' => $ticket->title,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'source' => $ticket->source,
            'assignee' => $ticket->assignee ? [
                'id' => $ticket->assignee->getKey(),
                'name' => $ticket->assignee->name,
            ] : null,
            'sla_due_at_label' => $ticket->sla_due_at ? \Illuminate\Support\Carbon::parse($ticket->sla_due_at)->diffForHumans() : null,
            'resolved_at_label' => $ticket->resolved_at ? \Illuminate\Support\Carbon::parse($ticket->resolved_at)->diffForHumans() : null,
        ];
    }

    protected function transformWebsite(Website $website): array
    {
        return [
            'id' => $website->getKey(),
            'name' => $website->name,
            'url' => $website->url,
            'cms' => $website->cms,
            'status' => $website->status,
            'domain' => $website->domain ? [
                'id' => $website->domain->id,
                'name' => $website->domain->domain_name,
                'registrar' => $website->domain->registrar,
                'expiry_date' => $website->domain->expiry_date?->format('d M Y'),
            ] : null,
            'server' => $website->server ? [
                'id' => $website->server->id,
                'name' => $website->server->name,
                'provider' => $website->server->provider,
                'ip_address' => $website->server->ip_address,
            ] : null,
        ];
    }

    protected function formatCurrency(Workspace $workspace, float $value): string
    {
        if ($value <= 0) {
            return 'Belum diisi';
        }

        $prefix = strtoupper((string) $workspace->currency) === 'IDR' ? 'Rp ' : strtoupper((string) $workspace->currency) . ' ';

        return $prefix . number_format($value, 0, ',', '.');
    }
}
