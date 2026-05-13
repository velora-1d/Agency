<?php

namespace App\Modules\Project\Projects\Queries;

use App\Http\Resources\LeadActivityResource;
use App\Models\ActivityFeed;
use App\Models\Client;
use App\Models\File;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\Note;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProjectIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);

        $projects = $this->projectQuery($workspace, $filters)->get();

        return [
            'projects' => [
                'summary' => $this->buildSummary($workspace, $projects),
                'items' => $projects->map(fn (Project $project): array => $this->transformProject($project, $workspace))->values()->all(),
            ],
            'projectTemplates' => $this->templateOptions($workspace),
            'filters' => $filters,
            'filterOptions' => $this->buildFilterOptions($workspace),
        ];
    }

    public function getShowPayload(Workspace $workspace, Project $project): array
    {
        abort_unless($project->workspace_id === $workspace->getKey(), 404);

        $project->load([
            'client:id,company_name,portal_access',
            'template:id,name,description,default_tasks',
            'creator:id,name',
            'members.user:id,name',
            'tasks' => fn ($query) => $query
                ->with(['assignee:id,name'])
                ->whereNull('parent_task_id')
                ->latest('created_at'),
            'files' => fn ($query) => $query->latest('created_at'),
            'notes' => fn ($query) => $query->latest('updated_at'),
            'meetings' => fn ($query) => $query->latest('scheduled_at'),
            'invoices' => fn ($query) => $query->latest('created_at'),
        ])->loadCount([
            'tasks',
            'files',
            'notes',
            'meetings',
            'invoices',
            'tasks as completed_tasks_count' => fn (Builder $query) => $query->where('status', 'done'),
            'files as pending_approvals_count' => fn (Builder $query) => $query->where('approval_status', 'pending'),
            'files as approved_deliverables_count' => fn (Builder $query) => $query->where('approval_status', 'approved'),
        ]);

        $activities = ActivityFeed::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('subject_type', Project::class)
            ->where('subject_id', $project->getKey())
            ->with(['user:id,name', 'comments.user:id,name'])
            ->latest('created_at')
            ->get();

        return [
            'project' => $this->transformProjectDetail($project, $workspace),
            'tabs' => [
                'tasks' => $project->tasks->map(fn (Task $task): array => $this->transformTask($task))->values()->all(),
                'files' => $project->files->map(fn (File $file): array => $this->transformFile($file))->values()->all(),
                'notes' => $project->notes->map(fn (Note $note): array => $this->transformNote($note))->values()->all(),
                'meetings' => $project->meetings->map(fn (Meeting $meeting): array => $this->transformMeeting($meeting))->values()->all(),
                'invoices' => $project->invoices->map(fn (Invoice $invoice): array => $this->transformInvoice($invoice, $workspace))->values()->all(),
            ],
            'activities' => LeadActivityResource::collection($activities)->resolve(),
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
            'search' => $normalize($input['search'] ?? null),
            'client' => $normalize($input['client'] ?? null),
            'status' => $normalize($input['status'] ?? null),
            'assignee' => $normalize($input['assignee'] ?? null),
            'deadline' => $normalize($input['deadline'] ?? null),
            'budget' => $normalize($input['budget'] ?? null),
        ];
    }

    /**
     * @param  array<string, string|null>  $filters
     */
    protected function projectQuery(Workspace $workspace, array $filters): Builder
    {
        $query = Project::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'client:id,company_name,portal_access',
                'template:id,name',
                'members.user:id,name',
            ])
            ->withCount([
                'tasks',
                'files',
                'notes',
                'meetings',
                'invoices',
                'tasks as completed_tasks_count' => fn (Builder $builder) => $builder->where('status', 'done'),
                'files as pending_approvals_count' => fn (Builder $builder) => $builder->where('approval_status', 'pending'),
            ])
            ->latest('created_at');

        $query
            ->when($filters['search'], function (Builder $builder, string $search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('client', fn (Builder $clientQuery) => $clientQuery->where('company_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['client'], fn (Builder $builder, string $clientId) => $builder->where('client_id', $clientId))
            ->when($filters['status'], fn (Builder $builder, string $status) => $builder->where('status', $status))
            ->when($filters['assignee'], fn (Builder $builder, string $userId) => $builder->whereHas('members', fn (Builder $memberQuery) => $memberQuery->where('user_id', $userId)));

        match ($filters['deadline']) {
            'overdue' => $query->whereDate('end_date', '<', now()->toDateString())->where('status', '!=', 'completed'),
            'next_7_days' => $query->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()]),
            'next_30_days' => $query->whereBetween('end_date', [now()->toDateString(), now()->addDays(30)->toDateString()]),
            'no_deadline' => $query->whereNull('end_date'),
            default => null,
        };

        match ($filters['budget']) {
            'under_10m' => $query->whereNotNull('budget')->where('budget', '<', 10000000),
            '10m_50m' => $query->whereBetween('budget', [10000000, 50000000]),
            'above_50m' => $query->where('budget', '>', 50000000),
            'no_budget' => $query->whereNull('budget'),
            default => null,
        };

        return $query;
    }

    protected function buildSummary(Workspace $workspace, Collection $projects): array
    {
        return [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'completed_projects' => $projects->where('status', 'completed')->count(),
            'overdue_projects' => $projects->filter(fn (Project $project): bool => $this->isOverdue($project))->count(),
            'total_budget_label' => $this->formatCurrency($workspace, (float) $projects->sum(fn (Project $project): float => (float) ($project->budget ?? 0))),
            'total_actual_cost_label' => $this->formatCurrency($workspace, (float) $projects->sum(fn (Project $project): float => (float) ($project->actual_cost ?? 0))),
        ];
    }

    protected function buildFilterOptions(Workspace $workspace): array
    {
        $clients = Client::query()
            ->where('workspace_id', $workspace->getKey())
            ->orderBy('company_name')
            ->get(['id', 'company_name'])
            ->map(fn (Client $client): array => [
                'id' => $client->getKey(),
                'name' => $client->company_name,
            ])
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
            'clients' => $clients,
            'assignees' => $assignees,
            'statuses' => [
                ['value' => 'planning', 'label' => 'Perencanaan'],
                ['value' => 'active', 'label' => 'Aktif'],
                ['value' => 'on_hold', 'label' => 'Ditunda'],
                ['value' => 'completed', 'label' => 'Selesai'],
            ],
            'deadlines' => [
                ['value' => 'overdue', 'label' => 'Terlambat'],
                ['value' => 'next_7_days', 'label' => '7 Hari Ke Depan'],
                ['value' => 'next_30_days', 'label' => '30 Hari Ke Depan'],
                ['value' => 'no_deadline', 'label' => 'Tanpa Deadline'],
            ],
            'budgets' => [
                ['value' => 'under_10m', 'label' => '< Rp 10 juta'],
                ['value' => '10m_50m', 'label' => 'Rp 10 - 50 juta'],
                ['value' => 'above_50m', 'label' => '> Rp 50 juta'],
                ['value' => 'no_budget', 'label' => 'No Budget'],
            ],
        ];
    }

    protected function templateOptions(Workspace $workspace): array
    {
        return ProjectTemplate::query()
            ->where('workspace_id', $workspace->getKey())
            ->orderBy('name')
            ->get()
            ->map(fn (ProjectTemplate $template): array => $this->transformTemplate($template))
            ->values()
            ->all();
    }

    protected function transformProject(Project $project, Workspace $workspace): array
    {
        $budget = (float) ($project->budget ?? 0);
        $actualCost = (float) ($project->actual_cost ?? 0);

        return [
            'id' => $project->getKey(),
            'brand' => $project->brand,
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status,
            'status_label' => $this->statusLabel($project->status),
            'start_date' => $project->start_date?->toDateString(),
            'end_date' => $project->end_date?->toDateString(),
            'start_date_label' => $project->start_date?->format('d M Y'),
            'end_date_label' => $project->end_date?->format('d M Y'),
            'timeline_label' => $this->timelineLabel($project),
            'timeline_state' => $this->timelineState($project),
            'progress' => (int) ($project->progress ?? 0),
            'budget' => $budget,
            'actual_cost' => $actualCost,
            'budget_label' => $this->formatCurrency($workspace, $budget),
            'actual_cost_label' => $this->formatCurrency($workspace, $actualCost),
            'remaining_budget_label' => $this->formatCurrency($workspace, max($budget - $actualCost, 0)),
            'tags' => collect($project->tags ?? [])->filter()->values()->all(),
            'client' => $project->client ? [
                'id' => $project->client->getKey(),
                'company_name' => $project->client->company_name,
                'portal_access' => (bool) $project->client->portal_access,
            ] : null,
            'template' => $project->template ? [
                'id' => $project->template->getKey(),
                'name' => $project->template->name,
            ] : null,
            'members' => $project->members->map(fn ($member): array => [
                'id' => $member->user?->getKey() ?? $member->user_id,
                'name' => $member->user?->name ?? 'Unknown member',
                'role' => $member->role,
                'initials' => $this->initials($member->user?->name),
            ])->values()->all(),
            'counts' => [
                'tasks' => $project->tasks_count ?? 0,
                'completed_tasks' => $project->completed_tasks_count ?? 0,
                'files' => $project->files_count ?? 0,
                'notes' => $project->notes_count ?? 0,
                'meetings' => $project->meetings_count ?? 0,
                'invoices' => $project->invoices_count ?? 0,
            ],
            'pending_approvals' => $project->pending_approvals_count ?? 0,
            'portal_enabled' => (bool) ($project->client?->portal_access ?? false),
            'created_at_label' => $project->created_at?->diffForHumans(),
        ];
    }

    protected function transformProjectDetail(Project $project, Workspace $workspace): array
    {
        $base = $this->transformProject($project, $workspace);
        $defaultTasks = collect($project->template?->default_tasks ?? [])
            ->map(fn (array $task): string => (string) ($task['title'] ?? ''))
            ->filter()
            ->values()
            ->all();

        return array_merge($base, [
            'creator' => $project->creator ? [
                'id' => $project->creator->getKey(),
                'name' => $project->creator->name,
            ] : null,
            'counts' => array_merge($base['counts'], [
                'approved_deliverables' => $project->approved_deliverables_count ?? 0,
                'pending_approvals' => $project->pending_approvals_count ?? 0,
                'activities' => ActivityFeed::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->where('subject_type', Project::class)
                    ->where('subject_id', $project->getKey())
                    ->count(),
            ]),
            'overview' => [
                'template_description' => $project->template?->description,
                'default_tasks' => $defaultTasks,
                'portal' => [
                    'enabled' => (bool) ($project->client?->portal_access ?? false),
                    'client_name' => $project->client?->company_name,
                    'invoice_download_count' => $project->invoices_count ?? 0,
                ],
                'approvals' => [
                    'pending' => $project->pending_approvals_count ?? 0,
                    'approved' => $project->approved_deliverables_count ?? 0,
                ],
            ],
        ]);
    }

    protected function transformTask(Task $task): array
    {
        return [
            'id' => $task->getKey(),
            'title' => $task->title,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_date_label' => $task->due_date?->format('d M Y H:i'),
            'due_date_human' => $task->due_date?->diffForHumans(),
            'estimated_hours' => $task->estimated_hours !== null ? (float) $task->estimated_hours : null,
            'actual_hours' => $task->actual_hours !== null ? (float) $task->actual_hours : null,
            'subtask_count' => $task->subTasks()->count(),
            'assignee' => $task->assignee ? [
                'id' => $task->assignee->getKey(),
                'name' => $task->assignee->name,
            ] : null,
        ];
    }

    protected function transformFile(File $file): array
    {
        return [
            'id' => $file->getKey(),
            'name' => $file->name,
            'original_name' => $file->original_name,
            'mime_type' => $file->mime_type,
            'approval_status' => $file->approval_status,
            'version' => $file->version,
            'size_label' => $this->formatBytes($file->size_bytes),
            'created_at_label' => $file->created_at?->diffForHumans(),
            'share_expires_at_label' => $file->share_expires_at?->format('d M Y H:i'),
        ];
    }

    protected function transformNote(Note $note): array
    {
        return [
            'id' => $note->getKey(),
            'title' => $note->title,
            'type' => $note->type,
            'is_private' => (bool) $note->is_private,
            'version' => $note->version,
            'updated_at_label' => $note->updated_at?->diffForHumans(),
        ];
    }

    protected function transformMeeting(Meeting $meeting): array
    {
        return [
            'id' => $meeting->getKey(),
            'title' => $meeting->title,
            'status' => $meeting->status,
            'scheduled_at_label' => $meeting->scheduled_at?->format('d M Y H:i'),
            'scheduled_at_human' => $meeting->scheduled_at?->diffForHumans(),
            'duration_minutes' => $meeting->duration_minutes,
            'meeting_url' => $meeting->meeting_url,
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

    protected function transformTemplate(ProjectTemplate $template): array
    {
        $tasks = collect($template->default_tasks ?? [])
            ->map(fn (array $task): string => (string) ($task['title'] ?? ''))
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $template->getKey(),
            'name' => $template->name,
            'description' => $template->description,
            'default_tasks' => $tasks,
            'default_tasks_text' => implode("\n", $tasks),
            'created_at_label' => $template->created_at?->diffForHumans(),
        ];
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'planning' => 'Perencanaan',
            'active' => 'Aktif',
            'on_hold' => 'Ditunda',
            'completed' => 'Selesai',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    protected function timelineLabel(Project $project): string
    {
        if (! $project->start_date && ! $project->end_date) {
            return 'Timeline belum diatur';
        }

        if ($project->start_date && $project->end_date) {
            return sprintf('%s - %s', $project->start_date->format('d M Y'), $project->end_date->format('d M Y'));
        }

        if ($project->start_date) {
            return sprintf('Mulai %s', $project->start_date->format('d M Y'));
        }

        return sprintf('Deadline %s', $project->end_date?->format('d M Y'));
    }

    protected function timelineState(Project $project): string
    {
        if ($this->isOverdue($project)) {
            return 'overdue';
        }

        if ($project->status === 'completed') {
            return 'completed';
        }

        if (! $project->end_date) {
            return 'unscheduled';
        }

        if ($project->end_date->isToday()) {
            return 'today';
        }

        return 'scheduled';
    }

    protected function isOverdue(Project $project): bool
    {
        return $project->status !== 'completed'
            && $project->end_date !== null
            && $project->end_date->isPast()
            && ! $project->end_date->isToday();
    }

    protected function formatCurrency(Workspace $workspace, float $value): string
    {
        if ($value <= 0) {
            return 'Belum diisi';
        }

        $prefix = strtoupper((string) $workspace->currency) === 'IDR'
            ? 'Rp '
            : strtoupper((string) $workspace->currency) . ' ';

        return $prefix . number_format($value, 0, ',', '.');
    }

    protected function formatBytes(?int $bytes): string
    {
        if (! $bytes || $bytes <= 0) {
            return 'Unknown size';
        }

        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    protected function initials(?string $name): string
    {
        if (! $name) {
            return 'NA';
        }

        return collect(explode(' ', $name))
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
            ->implode('');
    }
}
