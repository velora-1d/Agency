<?php

namespace App\Modules\Project\Files\Queries;

use App\Models\Client;
use App\Models\File;
use App\Models\FileFolder;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FileIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $roots = $this->rootFiles($workspace);
        $items = $this->transformFamilies($roots);
        $filteredItems = $this->applyFilters($items, $filters);

        return [
            'files' => [
                'summary' => $this->buildSummary($filteredItems, $workspace, $items),
                'items' => $filteredItems->values()->all(),
            ],
            'folders' => $this->folderPayload($workspace, $items),
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($workspace),
        ];
    }

    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'search' => $normalize($input['search'] ?? null),
            'project' => $normalize($input['project'] ?? null),
            'client' => $normalize($input['client'] ?? null),
            'folder' => $normalize($input['folder'] ?? null),
            'preview' => $normalize($input['preview'] ?? null),
            'approval' => $normalize($input['approval'] ?? null),
            'share' => $normalize($input['share'] ?? null),
        ];
    }

    protected function rootFiles(Workspace $workspace): Collection
    {
        return File::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereNull('parent_file_id')
            ->with([
                'project:id,name',
                'client:id,company_name',
                'folder:id,name',
                'uploader:id,name',
                'approver:id,name',
                'versions' => fn ($query) => $query
                    ->with(['project:id,name', 'client:id,company_name', 'folder:id,name', 'uploader:id,name', 'approver:id,name'])
                    ->orderByDesc('version')
                    ->orderByDesc('created_at'),
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    protected function transformFamilies(Collection $roots): Collection
    {
        return $roots->map(function (File $root): array {
            $family = collect([$root])
                ->concat($root->versions)
                ->sortByDesc(fn (File $file): array => [(int) $file->version, optional($file->created_at)?->timestamp ?? 0])
                ->values();

            /** @var File $current */
            $current = $family->first();
            $totalBytes = (int) $family->sum(fn (File $file): int => (int) ($file->size_bytes ?? 0));
            $activeShare = filled($current->share_token) && filled($current->share_expires_at) && $current->share_expires_at->isFuture();

            return [
                'id' => $current->getKey(),
                'family_id' => $root->getKey(),
                'name' => $current->name,
                'original_name' => $current->original_name,
                'project_id' => $current->project_id,
                'client_id' => $current->client_id,
                'folder_id' => $current->folder_id,
                'path' => $current->path,
                'mime_type' => $current->mime_type,
                'preview_kind' => $this->previewKind($current->mime_type),
                'storage_url' => $current->path ? Storage::disk('public')->url($current->path) : null,
                'size_bytes' => (int) ($current->size_bytes ?? 0),
                'size_label' => $this->formatBytes((int) ($current->size_bytes ?? 0)),
                'version' => (int) $current->version,
                'approval_status' => $current->approval_status ?: 'draft',
                'approval_label' => ucfirst($current->approval_status ?: 'draft'),
                'share_token' => $current->share_token,
                'share_expires_at' => $current->share_expires_at?->toIso8601String(),
                'share_expires_at_label' => $current->share_expires_at?->format('d M Y H:i'),
                'share_url' => $current->share_token ? route('files.public.show', ['token' => $current->share_token]) : null,
                'share_active' => $activeShare,
                'created_at' => $current->created_at?->toIso8601String(),
                'created_at_label' => $current->created_at?->diffForHumans(),
                'project' => $current->project ? [
                    'id' => $current->project->getKey(),
                    'name' => $current->project->name,
                ] : null,
                'client' => $current->client ? [
                    'id' => $current->client->getKey(),
                    'name' => $current->client->company_name,
                ] : null,
                'folder' => $current->folder ? [
                    'id' => $current->folder->getKey(),
                    'name' => $current->folder->name,
                ] : null,
                'uploader' => $current->uploader ? [
                    'id' => $current->uploader->getKey(),
                    'name' => $current->uploader->name,
                ] : null,
                'approver' => $current->approver ? [
                    'id' => $current->approver->getKey(),
                    'name' => $current->approver->name,
                ] : null,
                'counts' => [
                    'versions' => $family->count(),
                    'total_family_bytes' => $totalBytes,
                    'total_family_size_label' => $this->formatBytes($totalBytes),
                ],
                'versions' => $family->map(function (File $version, int $index): array {
                    return [
                        'id' => $version->getKey(),
                        'name' => $version->name,
                        'original_name' => $version->original_name,
                        'mime_type' => $version->mime_type,
                        'preview_kind' => $this->previewKind($version->mime_type),
                        'storage_url' => $version->path ? Storage::disk('public')->url($version->path) : null,
                        'size_bytes' => (int) ($version->size_bytes ?? 0),
                        'size_label' => $this->formatBytes((int) ($version->size_bytes ?? 0)),
                        'version' => (int) $version->version,
                        'approval_status' => $version->approval_status ?: 'draft',
                        'created_at' => $version->created_at?->toIso8601String(),
                        'created_at_label' => $version->created_at?->diffForHumans(),
                        'is_current' => $index === 0,
                        'uploader' => $version->uploader ? [
                            'id' => $version->uploader->getKey(),
                            'name' => $version->uploader->name,
                        ] : null,
                    ];
                })->values()->all(),
            ];
        })->values();
    }

    protected function applyFilters(Collection $items, array $filters): Collection
    {
        return $items->filter(function (array $item) use ($filters): bool {
            if ($filters['search']) {
                $haystack = strtolower(implode(' ', array_filter([
                    $item['name'] ?? null,
                    $item['original_name'] ?? null,
                    $item['project']['name'] ?? null,
                    $item['client']['name'] ?? null,
                    $item['folder']['name'] ?? null,
                    $item['mime_type'] ?? null,
                ])));

                if (! str_contains($haystack, strtolower($filters['search']))) {
                    return false;
                }
            }

            if ($filters['project'] && ($item['project_id'] ?? null) !== $filters['project']) {
                return false;
            }

            if ($filters['client'] && ($item['client_id'] ?? null) !== $filters['client']) {
                return false;
            }

            if ($filters['folder'] && ($item['folder_id'] ?? null) !== $filters['folder']) {
                return false;
            }

            if ($filters['preview'] && ($item['preview_kind'] ?? null) !== $filters['preview']) {
                return false;
            }

            if ($filters['approval'] && ($item['approval_status'] ?? null) !== $filters['approval']) {
                return false;
            }

            if ($filters['share'] === 'active' && ! ($item['share_active'] ?? false)) {
                return false;
            }

            if ($filters['share'] === 'expired') {
                if (! filled($item['share_expires_at'])) {
                    return false;
                }

                if ($item['share_active']) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    protected function buildSummary(Collection $filteredItems, Workspace $workspace, Collection $allItems): array
    {
        $allBytes = $allItems->sum(fn (array $item): int => (int) ($item['counts']['total_family_bytes'] ?? 0));
        $quotaBytes = max(1, ((int) $workspace->storage_quota_gb) * 1024 * 1024 * 1024);
        $usagePercent = min(100, (int) round(($allBytes / $quotaBytes) * 100));

        return [
            'total_files' => $filteredItems->count(),
            'image_files' => $filteredItems->where('preview_kind', 'image')->count(),
            'pdf_files' => $filteredItems->where('preview_kind', 'pdf')->count(),
            'video_files' => $filteredItems->where('preview_kind', 'video')->count(),
            'pending_approvals' => $filteredItems->where('approval_status', 'pending')->count(),
            'approved_files' => $filteredItems->where('approval_status', 'approved')->count(),
            'shared_files' => $filteredItems->where('share_active', true)->count(),
            'used_bytes' => $allBytes,
            'used_label' => $this->formatBytes($allBytes),
            'quota_bytes' => $quotaBytes,
            'quota_label' => $this->formatBytes($quotaBytes),
            'remaining_label' => $this->formatBytes(max(0, $quotaBytes - $allBytes)),
            'usage_percent' => $usagePercent,
        ];
    }

    protected function folderPayload(Workspace $workspace, Collection $items): array
    {
        $counts = $items->groupBy('folder_id')
            ->map(fn (Collection $group): int => $group->count());

        return FileFolder::query()
            ->where('workspace_id', $workspace->getKey())
            ->orderBy('name')
            ->get()
            ->map(fn (FileFolder $folder): array => [
                'id' => $folder->getKey(),
                'name' => $folder->name,
                'files_count' => (int) ($counts[$folder->getKey()] ?? 0),
            ])
            ->values()
            ->all();
    }

    protected function filterOptions(Workspace $workspace): array
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
            'clients' => Client::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('company_name')
                ->get(['id', 'company_name'])
                ->map(fn (Client $client): array => [
                    'id' => $client->getKey(),
                    'name' => $client->company_name,
                ])
                ->values()
                ->all(),
            'folders' => FileFolder::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (FileFolder $folder): array => [
                    'id' => $folder->getKey(),
                    'name' => $folder->name,
                ])
                ->values()
                ->all(),
            'previewKinds' => [
                ['value' => 'image', 'label' => 'Image'],
                ['value' => 'pdf', 'label' => 'PDF'],
                ['value' => 'video', 'label' => 'Video'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'approvalStatuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'approved', 'label' => 'Approved'],
                ['value' => 'rejected', 'label' => 'Rejected'],
            ],
            'shareStates' => [
                ['value' => 'active', 'label' => 'Active Share'],
                ['value' => 'expired', 'label' => 'Expired Share'],
            ],
        ];
    }

    protected function previewKind(?string $mimeType): string
    {
        $mimeType = strtolower((string) $mimeType);

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if ($mimeType === 'application/pdf') {
            return 'pdf';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        return 'other';
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 1) . ' ' . $units[$power];
    }
}
