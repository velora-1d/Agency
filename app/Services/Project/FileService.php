<?php

namespace App\Services\Project;

use App\Models\ActivityFeed;
use App\Models\Client;
use App\Models\File;
use App\Models\FileFolder;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public function create(Workspace $workspace, array $data, ?UploadedFile $binary): File
    {
        abort_if($binary === null, 422, 'Binary upload is required.');

        $project = $this->resolveProject($workspace, $data['project_id'] ?? null);
        $client = $this->resolveClient($workspace, $data['client_id'] ?? null);
        $folder = $this->resolveFolder($workspace, $data['folder_id'] ?? null);
        $source = $this->resolveSourceFile($workspace, $data['source_file_id'] ?? null);
        $path = $binary->store("files/{$workspace->slug}", 'public');

        [$parentFileId, $version] = $this->versionContext($source);

        $file = File::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project?->getKey(),
            'client_id' => $client?->getKey(),
            'folder_id' => $folder?->getKey(),
            'name' => $data['name'],
            'original_name' => $binary->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $binary->getMimeType(),
            'size_bytes' => $binary->getSize(),
            'version' => $version,
            'parent_file_id' => $parentFileId,
            'approval_status' => $data['approval_status'] ?? 'pending',
            'approved_by' => in_array($data['approval_status'] ?? 'pending', ['approved', 'rejected'], true) ? Auth::id() : null,
            'uploaded_by' => Auth::id(),
            'created_at' => now(),
        ]);

        $description = $source
            ? sprintf('Versi baru untuk file %s berhasil diunggah.', $file->name)
            : sprintf('File %s berhasil diunggah.', $file->name);

        $this->logActivity($workspace, $file, $description, $source ? 'version' : 'upload', $source ? 'sky' : 'emerald');

        return $file->refresh();
    }

    public function update(Workspace $workspace, File $file, array $data, ?UploadedFile $binary): File
    {
        abort_unless($file->workspace_id === $workspace->getKey(), 404);

        $project = $this->resolveProject($workspace, $data['project_id'] ?? null);
        $client = $this->resolveClient($workspace, $data['client_id'] ?? null);
        $folder = $this->resolveFolder($workspace, $data['folder_id'] ?? null);

        $attributes = [
            'project_id' => $project?->getKey(),
            'client_id' => $client?->getKey(),
            'folder_id' => $folder?->getKey(),
            'name' => $data['name'],
            'approval_status' => $data['approval_status'] ?? $file->approval_status,
            'approved_by' => in_array($data['approval_status'] ?? $file->approval_status, ['approved', 'rejected'], true)
                ? Auth::id()
                : null,
        ];

        if ($binary !== null) {
            if ($file->path) {
                Storage::disk('public')->delete($file->path);
            }

            $path = $binary->store("files/{$workspace->slug}", 'public');

            $attributes['original_name'] = $binary->getClientOriginalName();
            $attributes['path'] = $path;
            $attributes['mime_type'] = $binary->getMimeType();
            $attributes['size_bytes'] = $binary->getSize();
        }

        $file->update($attributes);

        $this->logActivity($workspace, $file, sprintf('Metadata file %s diperbarui.', $file->name), 'update', 'amber');

        return $file->refresh();
    }

    public function delete(Workspace $workspace, File $file): void
    {
        abort_unless($file->workspace_id === $workspace->getKey(), 404);

        $family = $this->familyRecords($workspace, $file);
        $title = $family->sortByDesc('version')->first()?->name ?: $file->name;

        $family->each(function (File $member): void {
            if ($member->path) {
                Storage::disk('public')->delete($member->path);
            }
        });

        File::query()
            ->whereIn('id', $family->pluck('id')->all())
            ->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'file',
            'subject_type' => File::class,
            'subject_id' => null,
            'description' => sprintf('File %s beserta version history dihapus.', $title),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    public function updateApproval(Workspace $workspace, File $file, string $status): File
    {
        abort_unless($file->workspace_id === $workspace->getKey(), 404);

        $file->update([
            'approval_status' => $status,
            'approved_by' => in_array($status, ['approved', 'rejected'], true) ? Auth::id() : null,
        ]);

        $this->logActivity($workspace, $file, sprintf('Approval file %s diubah ke %s.', $file->name, $status), 'approval', 'amber');

        return $file->refresh();
    }

    public function updateShare(Workspace $workspace, File $file, array $data): File
    {
        abort_unless($file->workspace_id === $workspace->getKey(), 404);

        $expiresAt = $data['share_expires_at'] ?? null;

        if (! $expiresAt) {
            $file->update([
                'share_token' => null,
                'share_expires_at' => null,
            ]);

            $this->logActivity($workspace, $file, sprintf('Share link file %s dinonaktifkan.', $file->name), 'share', 'slate');

            return $file->refresh();
        }

        $file->update([
            'share_token' => ($data['regenerate'] ?? false) || blank($file->share_token)
                ? Str::random(40)
                : $file->share_token,
            'share_expires_at' => $expiresAt,
        ]);

        $this->logActivity($workspace, $file, sprintf('Share link file %s diperbarui.', $file->name), 'share', 'sky');

        return $file->refresh();
    }

    public function createFolder(Workspace $workspace, array $data): FileFolder
    {
        return FileFolder::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $data['name'],
        ])->refresh();
    }

    public function updateFolder(Workspace $workspace, FileFolder $folder, array $data): FileFolder
    {
        abort_unless($folder->workspace_id === $workspace->getKey(), 404);

        $folder->update([
            'name' => $data['name'],
        ]);

        return $folder->refresh();
    }

    public function deleteFolder(Workspace $workspace, FileFolder $folder): void
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

    protected function resolveClient(Workspace $workspace, ?string $clientId): ?Client
    {
        if (! $clientId) {
            return null;
        }

        return Client::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($clientId);
    }

    protected function resolveFolder(Workspace $workspace, ?string $folderId): ?FileFolder
    {
        if (! $folderId) {
            return null;
        }

        return FileFolder::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($folderId);
    }

    protected function resolveSourceFile(Workspace $workspace, ?string $fileId): ?File
    {
        if (! $fileId) {
            return null;
        }

        return File::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($fileId);
    }

    protected function versionContext(?File $source): array
    {
        if ($source === null) {
            return [null, 1];
        }

        $rootId = $source->parent_file_id ?: $source->getKey();
        $currentVersion = File::query()
            ->where(fn ($query) => $query->where('id', $rootId)->orWhere('parent_file_id', $rootId))
            ->max('version');

        return [$rootId, ((int) $currentVersion) + 1];
    }

    protected function familyRecords(Workspace $workspace, File $file): Collection
    {
        $rootId = $file->parent_file_id ?: $file->getKey();

        return File::query()
            ->where('workspace_id', $workspace->getKey())
            ->where(fn ($query) => $query->where('id', $rootId)->orWhere('parent_file_id', $rootId))
            ->get();
    }

    protected function logActivity(
        Workspace $workspace,
        File $file,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'file',
            'subject_type' => File::class,
            'subject_id' => $file->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'upload' => 'FileUp',
                    'version' => 'CopyPlus',
                    'approval' => 'BadgeCheck',
                    'share' => 'Share2',
                    'update' => 'Pencil',
                    default => 'File',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }
}
