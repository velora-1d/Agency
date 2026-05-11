<?php

namespace App\Services\Project;

use App\Models\ActivityFeed;
use App\Models\Note;
use App\Models\NoteFolder;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class NoteService
{
    public function create(Workspace $workspace, array $data): Note
    {
        $project = $this->resolveProject($workspace, $data['project_id'] ?? null);
        $folder = $this->resolveFolder($workspace, $data['folder_id'] ?? null);

        $note = Note::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project?->getKey(),
            'folder_id' => $folder?->getKey(),
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'type' => $data['type'],
            'is_private' => (bool) ($data['is_private'] ?? false),
            'version' => 1,
            'created_by' => Auth::id(),
        ]);

        $this->createRevision($note);
        $this->syncLinkedTasks($workspace, $note, $data['linked_task_ids'] ?? []);
        $this->logActivity($workspace, $note, sprintf('Note %s berhasil dibuat.', $note->title), 'create', 'emerald');

        return $note->refresh()->load(['project', 'folder', 'creator', 'revisions.creator', 'linkedTasks.assignee']);
    }

    public function update(Workspace $workspace, Note $note, array $data): Note
    {
        abort_unless($note->workspace_id === $workspace->getKey(), 404);

        $project = $this->resolveProject($workspace, $data['project_id'] ?? null);
        $folder = $this->resolveFolder($workspace, $data['folder_id'] ?? null);

        $note->update([
            'project_id' => $project?->getKey(),
            'folder_id' => $folder?->getKey(),
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'type' => $data['type'],
            'is_private' => (bool) ($data['is_private'] ?? false),
            'version' => (int) $note->version + 1,
        ]);

        $this->createRevision($note->fresh());
        $this->syncLinkedTasks($workspace, $note, $data['linked_task_ids'] ?? []);
        $this->logActivity($workspace, $note, sprintf('Note %s diperbarui.', $note->title), 'update', 'amber');

        return $note->refresh()->load(['project', 'folder', 'creator', 'revisions.creator', 'linkedTasks.assignee']);
    }

    public function delete(Workspace $workspace, Note $note): void
    {
        abort_unless($note->workspace_id === $workspace->getKey(), 404);

        Task::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('sop_note_id', $note->getKey())
            ->update(['sop_note_id' => null]);

        $title = $note->title;
        $note->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'note',
            'subject_type' => Note::class,
            'subject_id' => null,
            'description' => sprintf('Note %s dihapus.', $title),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
        ]);
    }

    public function createFolder(Workspace $workspace, array $data): NoteFolder
    {
        return NoteFolder::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $data['name'],
        ])->refresh();
    }

    public function updateFolder(Workspace $workspace, NoteFolder $folder, array $data): NoteFolder
    {
        abort_unless($folder->workspace_id === $workspace->getKey(), 404);

        $folder->update([
            'name' => $data['name'],
        ]);

        return $folder->refresh();
    }

    public function deleteFolder(Workspace $workspace, NoteFolder $folder): void
    {
        abort_unless($folder->workspace_id === $workspace->getKey(), 404);

        $folder->delete();
    }

    protected function resolveProject(Workspace $workspace, ?string $projectId): ?Project
    {
        if (! $projectId) {
            return null;
        }

        return Project::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($projectId);
    }

    protected function resolveFolder(Workspace $workspace, ?string $folderId): ?NoteFolder
    {
        if (! $folderId) {
            return null;
        }

        return NoteFolder::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($folderId);
    }

    protected function syncLinkedTasks(Workspace $workspace, Note $note, array $linkedTaskIds): void
    {
        $ids = collect($linkedTaskIds)
            ->filter()
            ->map(function (mixed $taskId) use ($workspace): string {
                $resolvedId = (string) $taskId;

                $exists = Task::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->whereKey($resolvedId)
                    ->exists();

                abort_unless($exists, 422);

                return $resolvedId;
            })
            ->unique()
            ->values()
            ->all();

        Task::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('sop_note_id', $note->getKey())
            ->whereNotIn('id', $ids ?: ['00000000-0000-0000-0000-000000000000'])
            ->update(['sop_note_id' => null]);

        if ($ids === []) {
            return;
        }

        Task::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereIn('id', $ids)
            ->update(['sop_note_id' => $note->getKey()]);
    }

    protected function createRevision(Note $note): void
    {
        $note->revisions()->create([
            'title' => $note->title,
            'content' => $note->content,
            'version' => $note->version,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);
    }

    protected function logActivity(
        Workspace $workspace,
        Note $note,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'note',
            'subject_type' => Note::class,
            'subject_id' => $note->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'NotebookPen',
                    'update' => 'Pencil',
                    default => 'Notebook',
                },
                'color' => $color,
            ],
        ]);
    }
}
