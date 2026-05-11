<?php

namespace App\Modules\Project\Notes\Queries;

use App\Models\Note;
use App\Models\NoteFolder;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NoteIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);

        $notes = $this->noteQuery($workspace, $filters)->get();
        $folders = $this->folderPayload($workspace);

        return [
            'notes' => [
                'summary' => $this->buildSummary($notes),
                'items' => $notes->map(fn (Note $note): array => $this->transformNote($note))->values()->all(),
                'selected_id' => $filters['note'],
            ],
            'folders' => $folders,
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($workspace, $folders),
        ];
    }

    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'search' => $normalize($input['search'] ?? null),
            'project' => $normalize($input['project'] ?? null),
            'folder' => $normalize($input['folder'] ?? null),
            'type' => $normalize($input['type'] ?? null),
            'visibility' => $normalize($input['visibility'] ?? null),
            'note' => $normalize($input['note'] ?? null),
        ];
    }

    protected function noteQuery(Workspace $workspace, array $filters): Builder
    {
        return Note::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'project:id,name',
                'folder:id,name',
                'creator:id,name',
                'revisions' => fn ($query) => $query->with('creator:id,name')->latest('version')->limit(8),
                'linkedTasks:id,workspace_id,project_id,sop_note_id,assigned_to,title,status,priority,due_date',
                'linkedTasks.assignee:id,name',
            ])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('folder', fn (Builder $folderQuery) => $folderQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['project'] === 'global', fn (Builder $query) => $query->whereNull('project_id'))
            ->when($filters['project'] && $filters['project'] !== 'global', fn (Builder $query, string $projectId) => $query->where('project_id', $projectId))
            ->when($filters['folder'], fn (Builder $query, string $folderId) => $query->where('folder_id', $folderId))
            ->when($filters['type'], fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['visibility'], function (Builder $query, string $visibility): void {
                if ($visibility === 'private') {
                    $query->where('is_private', true);
                    return;
                }

                if ($visibility === 'team') {
                    $query->where('is_private', false);
                }
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at');
    }

    protected function buildSummary(Collection $notes): array
    {
        return [
            'total_notes' => $notes->count(),
            'sop_notes' => $notes->where('type', 'sop')->count(),
            'template_notes' => $notes->where('type', 'template')->count(),
            'private_notes' => $notes->where('is_private', true)->count(),
            'global_notes' => $notes->whereNull('project_id')->count(),
            'linked_tasks' => $notes->sum(fn (Note $note): int => $note->linkedTasks->count()),
        ];
    }

    protected function folderPayload(Workspace $workspace): array
    {
        return NoteFolder::query()
            ->where('workspace_id', $workspace->getKey())
            ->withCount('notes')
            ->orderBy('name')
            ->get()
            ->map(fn (NoteFolder $folder): array => [
                'id' => $folder->getKey(),
                'name' => $folder->name,
                'notes_count' => $folder->notes_count ?? 0,
            ])
            ->values()
            ->all();
    }

    protected function filterOptions(Workspace $workspace, array $folders): array
    {
        return [
            'projects' => Project::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Project $project): array => [
                    'id' => $project->getKey(),
                    'name' => $project->name,
                ])
                ->values()
                ->all(),
            'folders' => $folders,
            'types' => [
                ['value' => 'note', 'label' => 'Note'],
                ['value' => 'sop', 'label' => 'SOP'],
                ['value' => 'template', 'label' => 'Template'],
            ],
            'visibility' => [
                ['value' => 'team', 'label' => 'Shared to Team'],
                ['value' => 'private', 'label' => 'Private'],
            ],
            'tasks' => Task::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('title')
                ->get(['id', 'title', 'project_id', 'sop_note_id'])
                ->map(fn (Task $task): array => [
                    'id' => $task->getKey(),
                    'title' => $task->title,
                    'project_id' => $task->project_id,
                    'sop_note_id' => $task->sop_note_id,
                ])
                ->values()
                ->all(),
            'users' => $workspace->users()
                ->select('users.id', 'users.name')
                ->orderBy('users.name')
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                ])
                ->values()
                ->all(),
        ];
    }

    protected function transformNote(Note $note): array
    {
        $content = (string) ($note->content ?? '');

        return [
            'id' => $note->getKey(),
            'project_id' => $note->project_id,
            'folder_id' => $note->folder_id,
            'title' => $note->title,
            'content' => $content,
            'content_preview' => trim((string) str($content)->stripTags()->squish()->limit(180)),
            'type' => $note->type,
            'type_label' => strtoupper($note->type),
            'is_private' => (bool) $note->is_private,
            'visibility_label' => $note->is_private ? 'Private' : 'Shared to Team',
            'version' => (int) $note->version,
            'updated_at_label' => $note->updated_at?->diffForHumans(),
            'project' => $note->project ? [
                'id' => $note->project->getKey(),
                'name' => $note->project->name,
            ] : null,
            'folder' => $note->folder ? [
                'id' => $note->folder->getKey(),
                'name' => $note->folder->name,
            ] : null,
            'creator' => $note->creator ? [
                'id' => $note->creator->getKey(),
                'name' => $note->creator->name,
            ] : null,
            'linked_tasks' => $note->linkedTasks->map(fn (Task $task): array => [
                'id' => $task->getKey(),
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date_label' => $task->due_date?->format('d M Y H:i'),
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->getKey(),
                    'name' => $task->assignee->name,
                ] : null,
            ])->values()->all(),
            'revisions' => $note->revisions->map(fn ($revision): array => [
                'id' => $revision->getKey(),
                'title' => $revision->title,
                'version' => (int) $revision->version,
                'content_preview' => trim((string) str((string) ($revision->content ?? ''))->stripTags()->squish()->limit(120)),
                'created_at_label' => $revision->created_at?->diffForHumans(),
                'creator' => $revision->creator ? [
                    'id' => $revision->creator->getKey(),
                    'name' => $revision->creator->name,
                ] : null,
            ])->values()->all(),
            'counts' => [
                'linked_tasks' => $note->linkedTasks->count(),
                'revisions' => $note->revisions->count(),
            ],
        ];
    }
}
