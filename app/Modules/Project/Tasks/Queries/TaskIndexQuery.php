<?php

namespace App\Modules\Project\Tasks\Queries;

use App\Http\Resources\LeadActivityResource;
use App\Models\ActivityFeed;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTemplate;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaskIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);

        $tasks = $this->taskQuery($workspace, $filters)->get();

        return [
            'tasks' => [
                'summary' => $this->buildSummary($tasks),
                'items' => $tasks->map(fn (Task $task): array => $this->transformTask($task))->values()->all(),
            ],
            'taskTemplates' => $this->taskTemplates($workspace),
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($workspace),
        ];
    }

    /**
     * @param array<string,mixed> $input
     * @return array<string,string|null>
     */
    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'search' => $normalize($input['search'] ?? null),
            'project' => $normalize($input['project'] ?? null),
            'status' => $normalize($input['status'] ?? null),
            'assignee' => $normalize($input['assignee'] ?? null),
            'priority' => $normalize($input['priority'] ?? null),
            'recurring' => $normalize($input['recurring'] ?? null),
        ];
    }

    /**
     * @param array<string,string|null> $filters
     */
    protected function taskQuery(Workspace $workspace, array $filters): Builder
    {
        return Task::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'project:id,name',
                'parentTask:id,title',
                'assignee:id,name',
                'dependencies:id,title',
                'template:id,title',
                'subTasks:id,parent_task_id,title,status',
                'timeLogs:id,user_id,task_id,hours,started_at,ended_at,notes',
                'activityFeed' => fn ($query) => $query
                    ->with('user:id,name')
                    ->latest('created_at'),
            ])
            ->withCount(['subTasks', 'dependencies'])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['project'], fn (Builder $query, string $projectId) => $query->where('project_id', $projectId))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['assignee'], fn (Builder $query, string $assigneeId) => $query->where('assigned_to', $assigneeId))
            ->when($filters['priority'], fn (Builder $query, string $priority) => $query->where('priority', $priority))
            ->when($filters['recurring'], function (Builder $query, string $recurring): void {
                if ($recurring === 'yes') {
                    $query->where('is_recurring', true);
                    return;
                }

                if ($recurring === 'no') {
                    $query->where('is_recurring', false);
                }
            })
            ->orderByRaw("case when due_date is null then 1 else 0 end")
            ->orderBy('due_date')
            ->orderBy('created_at');
    }

    protected function buildSummary(Collection $tasks): array
    {
        return [
            'total_tasks' => $tasks->count(),
            'todo_tasks' => $tasks->where('status', 'todo')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'review_tasks' => $tasks->where('status', 'review')->count(),
            'done_tasks' => $tasks->where('status', 'done')->count(),
            'overdue_tasks' => $tasks->filter(fn (Task $task): bool => $this->isOverdue($task))->count(),
            'tracked_hours' => number_format((float) $tasks->sum(fn (Task $task): float => (float) ($task->actual_hours ?? 0)), 1),
        ];
    }

    protected function filterOptions(Workspace $workspace): array
    {
        $projects = Project::query()
            ->where('workspace_id', $workspace->getKey())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Project $project): array => [
                'id' => $project->getKey(),
                'name' => $project->name,
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

        $parentTasks = Task::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereNull('parent_task_id')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Task $task): array => [
                'id' => $task->getKey(),
                'title' => $task->title,
            ])
            ->values()
            ->all();

        return [
            'projects' => $projects,
            'assignees' => $assignees,
            'parentTasks' => $parentTasks,
            'statuses' => [
                ['value' => 'todo', 'label' => 'To Do'],
                ['value' => 'in_progress', 'label' => 'In Progress'],
                ['value' => 'review', 'label' => 'Review'],
                ['value' => 'done', 'label' => 'Done'],
            ],
            'priorities' => [
                ['value' => 'low', 'label' => 'Low'],
                ['value' => 'medium', 'label' => 'Medium'],
                ['value' => 'high', 'label' => 'High'],
                ['value' => 'urgent', 'label' => 'Urgent'],
            ],
            'recurrenceRules' => [
                ['value' => 'daily', 'label' => 'Daily'],
                ['value' => 'weekly', 'label' => 'Weekly'],
                ['value' => 'monthly', 'label' => 'Monthly'],
            ],
        ];
    }

    protected function taskTemplates(Workspace $workspace): array
    {
        return TaskTemplate::query()
            ->where('workspace_id', $workspace->getKey())
            ->orderBy('title')
            ->get()
            ->map(fn (TaskTemplate $template): array => [
                'id' => $template->getKey(),
                'title' => $template->title,
                'description' => $template->description,
            ])
            ->values()
            ->all();
    }

    protected function transformTask(Task $task): array
    {
        $activities = $task->activityFeed->values();

        return [
            'id' => $task->getKey(),
            'project_id' => $task->project_id,
            'parent_task_id' => $task->parent_task_id,
            'assigned_to' => $task->assigned_to,
            'template_id' => $task->template_id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'status_label' => $this->statusLabel($task->status),
            'priority' => $task->priority,
            'priority_label' => ucfirst($task->priority),
            'tags' => collect($task->tags ?? [])->filter()->values()->all(),
            'due_date' => $task->due_date?->toIso8601String(),
            'due_date_label' => $task->due_date?->format('d M Y H:i'),
            'due_date_human' => $task->due_date?->diffForHumans(),
            'estimated_hours' => $task->estimated_hours !== null ? (float) $task->estimated_hours : null,
            'actual_hours' => $task->actual_hours !== null ? (float) $task->actual_hours : null,
            'order_index' => $task->order_index,
            'is_recurring' => (bool) $task->is_recurring,
            'recurrence_rule' => $task->recurrence_rule,
            'project' => $task->project ? [
                'id' => $task->project->getKey(),
                'name' => $task->project->name,
            ] : null,
            'parent_task' => $task->parentTask ? [
                'id' => $task->parentTask->getKey(),
                'title' => $task->parentTask->title,
            ] : null,
            'assignee' => $task->assignee ? [
                'id' => $task->assignee->getKey(),
                'name' => $task->assignee->name,
                'initials' => $this->initials($task->assignee->name),
            ] : null,
            'template' => $task->template ? [
                'id' => $task->template->getKey(),
                'title' => $task->template->title,
            ] : null,
            'dependencies' => $task->dependencies->map(fn (Task $dependency): array => [
                'id' => $dependency->getKey(),
                'title' => $dependency->title,
            ])->values()->all(),
            'subtasks' => $task->subTasks->map(fn (Task $subTask): array => [
                'id' => $subTask->getKey(),
                'title' => $subTask->title,
                'status' => $subTask->status,
            ])->values()->all(),
            'counts' => [
                'subtasks' => $task->sub_tasks_count ?? 0,
                'dependencies' => $task->dependencies_count ?? 0,
                'comments' => $activities->count(),
            ],
            'time_logs' => $task->timeLogs->map(fn ($log): array => [
                'id' => $log->getKey(),
                'started_at' => $log->started_at?->toIso8601String(),
                'started_at_label' => $log->started_at?->format('d M Y H:i'),
                'ended_at' => $log->ended_at?->toIso8601String(),
                'ended_at_label' => $log->ended_at?->format('d M Y H:i'),
                'hours' => $log->hours !== null ? (float) $log->hours : null,
                'notes' => $log->notes,
            ])->values()->all(),
            'comments' => LeadActivityResource::collection($activities)->resolve(),
            'gantt' => [
                'start' => $task->created_at?->toDateString(),
                'end' => $task->due_date?->toDateString(),
            ],
            'is_overdue' => $this->isOverdue($task),
        ];
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

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'review' => 'Review',
            'done' => 'Done',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    protected function isOverdue(Task $task): bool
    {
        return $task->status !== 'done'
            && $task->due_date !== null
            && $task->due_date->isPast();
    }
}
