<?php

namespace App\Services\Project;

use App\Models\ActivityFeed;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    public function create(Workspace $workspace, array $data): Project
    {
        $clientId = $this->resolveClient($workspace, $data['client_id'] ?? null);
        $template = $this->resolveTemplate($workspace, $data['template_id'] ?? null);

        $project = Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'brand' => $data['brand'] ?? 'Kantor Digital',
            'client_id' => $clientId,
            'template_id' => $template?->getKey(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'budget' => $data['budget'] ?? null,
            'actual_cost' => $data['actual_cost'] ?? 0,
            'tags' => $this->normalizeTags($data['tags'] ?? []),
            'created_by' => Auth::id(),
        ]);

        $this->syncMembers($workspace, $project, $data['members'] ?? []);
        $this->createDefaultTasksFromTemplate($project, $template);
        $project->refresh()->load(['members.user', 'client', 'template']);

        $this->logActivity($workspace, $project, sprintf('Project %s berhasil dibuat.', $project->name), 'create', 'emerald');

        return $project;
    }

    public function update(Workspace $workspace, Project $project, array $data): Project
    {
        abort_unless($project->workspace_id === $workspace->getKey(), 404);

        $clientId = $this->resolveClient($workspace, $data['client_id'] ?? null);
        $template = $this->resolveTemplate($workspace, $data['template_id'] ?? null);

        $project->update([
            'brand' => $data['brand'] ?? $project->brand,
            'client_id' => $clientId,
            'template_id' => $template?->getKey(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'budget' => $data['budget'] ?? null,
            'actual_cost' => $data['actual_cost'] ?? 0,
            'tags' => $this->normalizeTags($data['tags'] ?? []),
        ]);

        $this->syncMembers($workspace, $project, $data['members'] ?? []);

        $this->logActivity($workspace, $project, sprintf('Project %s diperbarui.', $project->name), 'update', 'amber');

        return $project->refresh()->load(['members.user', 'client', 'template']);
    }

    public function delete(Workspace $workspace, Project $project): void
    {
        abort_unless($project->workspace_id === $workspace->getKey(), 404);

        $projectName = $project->name;
        $project->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'project',
            'subject_type' => Project::class,
            'subject_id' => null,
            'description' => sprintf('Project %s dihapus.', $projectName),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
        ]);
    }

    public function updateStatus(Workspace $workspace, Project $project, string $status): Project
    {
        abort_unless($project->workspace_id === $workspace->getKey(), 404);

        if ($project->status === $status) {
            return $project;
        }

        $from = $project->status;

        $project->update([
            'status' => $status,
        ]);

        $this->logActivity(
            $workspace,
            $project,
            sprintf('Status project %s dipindahkan dari %s ke %s.', $project->name, $from, $status),
            'status',
            'blue'
        );

        return $project->refresh();
    }

    public function createTemplate(Workspace $workspace, array $data): ProjectTemplate
    {
        $template = ProjectTemplate::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'default_tasks' => $this->parseDefaultTasks($data['default_tasks_text'] ?? null),
            'default_finance_split' => null,
        ]);

        return $template->refresh();
    }

    public function updateTemplate(Workspace $workspace, ProjectTemplate $template, array $data): ProjectTemplate
    {
        abort_unless($template->workspace_id === $workspace->getKey(), 404);

        $template->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'default_tasks' => $this->parseDefaultTasks($data['default_tasks_text'] ?? null),
        ]);

        return $template->refresh();
    }

    public function deleteTemplate(Workspace $workspace, ProjectTemplate $template): void
    {
        abort_unless($template->workspace_id === $workspace->getKey(), 404);

        $template->delete();
    }

    protected function resolveClient(Workspace $workspace, ?string $clientId): ?string
    {
        if (! $clientId) {
            return null;
        }

        $exists = Client::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereKey($clientId)
            ->exists();

        abort_unless($exists, 422);

        return $clientId;
    }

    protected function resolveTemplate(Workspace $workspace, ?string $templateId): ?ProjectTemplate
    {
        if (! $templateId) {
            return null;
        }

        return ProjectTemplate::query()
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

    protected function syncMembers(Workspace $workspace, Project $project, array $members): void
    {
        $rows = collect($members)
            ->filter(fn (mixed $member): bool => is_array($member) && filled($member['user_id'] ?? null))
            ->map(function (array $member) use ($workspace): array {
                $userId = (string) $member['user_id'];
                $exists = $workspace->users()->where('users.id', $userId)->exists();
                abort_unless($exists, 422);

                return [
                    'user_id' => $userId,
                    'role' => filled($member['role'] ?? null) ? trim((string) $member['role']) : null,
                    'joined_at' => now(),
                ];
            })
            ->unique('user_id')
            ->values();

        $project->members()->delete();

        if ($rows->isNotEmpty()) {
            $project->members()->createMany($rows->all());
        }
    }

    protected function createDefaultTasksFromTemplate(Project $project, ?ProjectTemplate $template): void
    {
        if (! $template || $project->tasks()->exists()) {
            return;
        }

        $tasks = collect($template->default_tasks ?? [])
            ->map(fn (array $task, int $index): array => [
                'workspace_id' => $project->workspace_id,
                'project_id' => $project->getKey(),
                'title' => (string) ($task['title'] ?? ''),
                'description' => $task['description'] ?? null,
                'status' => 'todo',
                'priority' => $task['priority'] ?? 'medium',
                'order_index' => $index + 1,
                'created_by' => Auth::id(),
            ])
            ->filter(fn (array $task): bool => filled($task['title']))
            ->values();

        $tasks->each(fn (array $task): Task => Task::query()->create($task));
    }

    /**
     * @return array<int, array{title: string, description: null, priority: string}>
     */
    protected function parseDefaultTasks(?string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $text) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $title): array => [
                'title' => $title,
                'description' => null,
                'priority' => 'medium',
            ])
            ->all();
    }

    protected function logActivity(
        Workspace $workspace,
        Project $project,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'project',
            'subject_type' => Project::class,
            'subject_id' => $project->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'FolderPlus',
                    'update' => 'Pencil',
                    'status' => 'MoveRight',
                    default => 'FolderKanban',
                },
                'color' => $color,
            ],
        ]);
    }
}
