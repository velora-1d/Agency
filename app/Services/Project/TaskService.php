<?php

namespace App\Services\Project;

use App\Models\ActivityFeed;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTemplate;
use App\Models\TaskTimeLog;
use App\Models\Workspace;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    public function create(Workspace $workspace, array $data): Task
    {
        $project = $this->resolveProject($workspace, $data['project_id']);
        $parentTask = $this->resolveParentTask($workspace, $project, $data['parent_task_id'] ?? null);

        $task = Task::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'parent_task_id' => $parentTask?->getKey(),
            'assigned_to' => $this->resolveAssignee($workspace, $data['assigned_to'] ?? null),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'priority' => $data['priority'],
            'tags' => $this->normalizeTags($data['tags'] ?? []),
            'due_date' => $data['due_date'] ?? null,
            'estimated_hours' => $data['estimated_hours'] ?? null,
            'actual_hours' => $data['actual_hours'] ?? 0,
            'is_recurring' => (bool) ($data['is_recurring'] ?? false),
            'recurrence_rule' => ! empty($data['is_recurring']) ? ($data['recurrence_rule'] ?? null) : null,
            'template_id' => $this->resolveTemplate($workspace, $data['template_id'] ?? null)?->getKey(),
            'sop_note_id' => $data['sop_note_id'] ?? null,
            'created_by' => Auth::id(),
            'order_index' => (int) (Task::query()
                ->where('workspace_id', $workspace->getKey())
                ->where('project_id', $project->getKey())
                ->max('order_index') ?? 0) + 1,
        ]);

        $this->syncDependencies($workspace, $project, $task, $data['dependency_ids'] ?? []);
        $this->logActivity($workspace, $task, sprintf('Task %s berhasil dibuat.', $task->title), 'create', 'emerald');

        return $task->refresh();
    }

    public function update(Workspace $workspace, Task $task, array $data): Task
    {
        abort_unless($task->workspace_id === $workspace->getKey(), 404);

        $project = $this->resolveProject($workspace, $data['project_id']);
        $parentTask = $this->resolveParentTask($workspace, $project, $data['parent_task_id'] ?? null, $task);

        $task->update([
            'project_id' => $project->getKey(),
            'parent_task_id' => $parentTask?->getKey(),
            'assigned_to' => $this->resolveAssignee($workspace, $data['assigned_to'] ?? null),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'priority' => $data['priority'],
            'tags' => $this->normalizeTags($data['tags'] ?? []),
            'due_date' => $data['due_date'] ?? null,
            'estimated_hours' => $data['estimated_hours'] ?? null,
            'actual_hours' => $data['actual_hours'] ?? 0,
            'is_recurring' => (bool) ($data['is_recurring'] ?? false),
            'recurrence_rule' => ! empty($data['is_recurring']) ? ($data['recurrence_rule'] ?? null) : null,
            'template_id' => $this->resolveTemplate($workspace, $data['template_id'] ?? null)?->getKey(),
            'sop_note_id' => $data['sop_note_id'] ?? null,
        ]);

        $this->syncDependencies($workspace, $project, $task, $data['dependency_ids'] ?? []);
        $this->logActivity($workspace, $task, sprintf('Task %s diperbarui.', $task->title), 'update', 'amber');

        return $task->refresh();
    }

    public function updateStatus(Workspace $workspace, Task $task, string $status): Task
    {
        abort_unless($task->workspace_id === $workspace->getKey(), 404);

        if ($task->status === $status) {
            return $task;
        }

        $from = $task->status;

        $task->update(['status' => $status]);

        $this->logActivity(
            $workspace,
            $task,
            sprintf('Status task %s dipindahkan dari %s ke %s.', $task->title, $from, $status),
            'status',
            'blue'
        );

        return $task->refresh();
    }

    public function delete(Workspace $workspace, Task $task): void
    {
        abort_unless($task->workspace_id === $workspace->getKey(), 404);

        $title = $task->title;
        $task->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'task',
            'subject_type' => Task::class,
            'subject_id' => null,
            'description' => sprintf('Task %s dihapus.', $title),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
        ]);
    }

    public function addTimeLog(Workspace $workspace, Task $task, array $data): TaskTimeLog
    {
        abort_unless($task->workspace_id === $workspace->getKey(), 404);

        $hours = $data['hours'] ?? $this->calculateHours($data['started_at'], $data['ended_at'] ?? null);

        $log = $task->timeLogs()->create([
            'user_id' => Auth::id(),
            'started_at' => $data['started_at'],
            'ended_at' => $data['ended_at'] ?? null,
            'hours' => $hours,
            'notes' => $data['notes'] ?? null,
        ]);

        $task->update([
            'actual_hours' => (float) $task->timeLogs()->sum('hours'),
        ]);

        $this->logActivity($workspace, $task, sprintf('Time log ditambahkan ke task %s.', $task->title), 'time_log', 'emerald');

        return $log;
    }

    public function addComment(Workspace $workspace, Task $task, string $content): ActivityFeed
    {
        abort_unless($task->workspace_id === $workspace->getKey(), 404);

        $mentions = collect(preg_match_all('/@([\pL\pN._-]+)/u', $content, $matches) ? ($matches[1] ?? []) : [])
            ->filter()
            ->values()
            ->all();

        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'task_comment',
            'subject_type' => Task::class,
            'subject_id' => $task->getKey(),
            'description' => $content,
            'metadata' => [
                'action' => 'comment',
                'icon' => 'MessageSquare',
                'color' => 'stone',
                'mentions' => $mentions,
            ],
        ]);
    }

    public function createTemplate(Workspace $workspace, array $data): TaskTemplate
    {
        return TaskTemplate::query()->create([
            'workspace_id' => $workspace->getKey(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ])->refresh();
    }

    public function updateTemplate(Workspace $workspace, TaskTemplate $template, array $data): TaskTemplate
    {
        abort_unless($template->workspace_id === $workspace->getKey(), 404);

        $template->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        return $template->refresh();
    }

    public function deleteTemplate(Workspace $workspace, TaskTemplate $template): void
    {
        abort_unless($template->workspace_id === $workspace->getKey(), 404);

        $template->delete();
    }

    protected function resolveProject(Workspace $workspace, string $projectId): Project
    {
        return Project::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($projectId);
    }

    protected function resolveParentTask(Workspace $workspace, Project $project, ?string $parentTaskId, ?Task $currentTask = null): ?Task
    {
        if (! $parentTaskId) {
            return null;
        }

        $parent = Task::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('project_id', $project->getKey())
            ->findOrFail($parentTaskId);

        abort_if($currentTask && $currentTask->getKey() === $parent->getKey(), 422, 'Task cannot be its own parent.');

        return $parent;
    }

    protected function resolveAssignee(Workspace $workspace, ?string $assigneeId): ?string
    {
        if (! $assigneeId) {
            return null;
        }

        $exists = $workspace->users()->where('users.id', $assigneeId)->exists();
        abort_unless($exists, 422);

        return $assigneeId;
    }

    protected function resolveTemplate(Workspace $workspace, ?string $templateId): ?TaskTemplate
    {
        if (! $templateId) {
            return null;
        }

        return TaskTemplate::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($templateId);
    }

    protected function normalizeTags(array $tags): array
    {
        return collect($tags)
            ->map(fn (mixed $tag): string => trim((string) $tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function syncDependencies(Workspace $workspace, Project $project, Task $task, array $dependencyIds): void
    {
        $ids = collect($dependencyIds)
            ->filter()
            ->map(function (mixed $dependencyId) use ($workspace, $project, $task): string {
                $resolvedId = (string) $dependencyId;

                abort_if($resolvedId === $task->getKey(), 422, 'Task cannot depend on itself.');

                $exists = Task::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->where('project_id', $project->getKey())
                    ->whereKey($resolvedId)
                    ->exists();

                abort_unless($exists, 422);

                return $resolvedId;
            })
            ->unique()
            ->values()
            ->all();

        $task->dependencies()->sync($ids);
    }

    protected function calculateHours(string $startedAt, ?string $endedAt): ?float
    {
        if (! $endedAt) {
            return null;
        }

        $minutes = Carbon::parse($startedAt)->diffInMinutes(Carbon::parse($endedAt));

        return round($minutes / 60, 2);
    }

    protected function logActivity(
        Workspace $workspace,
        Task $task,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'task',
            'subject_type' => Task::class,
            'subject_id' => $task->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'ListTodo',
                    'update' => 'Pencil',
                    'status' => 'MoveRight',
                    'time_log' => 'Clock3',
                    default => 'MessageSquare',
                },
                'color' => $color,
            ],
        ]);
    }
}
