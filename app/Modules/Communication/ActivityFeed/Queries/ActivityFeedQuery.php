<?php

namespace App\Modules\Communication\ActivityFeed\Queries;

use App\Models\ActivityFeed;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityFeedQuery
{
    public function forWorkspace(Workspace $workspace, ?array $filters = []): LengthAwarePaginator
    {
        $query = ActivityFeed::where('workspace_id', $workspace->id)
            ->with(['user', 'comments.user', 'subject'])
            ->latest('created_at');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate(15);
    }

    public function getFilterOptions(Workspace $workspace): array
    {
        return [
            'types' => ActivityFeed::where('workspace_id', $workspace->id)
                ->distinct()
                ->pluck('type'),
            'users' => User::whereHas('workspaces', function ($q) use ($workspace) {
                $q->where('workspaces.id', $workspace->id);
            })->get(['id', 'name']),
        ];
    }
}
