<?php

namespace App\Support\Dashboard;

use App\Models\User;
use App\Models\WorkspaceUser;

class DashboardRole
{
    public static function current(?string $workspaceId, ?User $user = null): ?string
    {
        $user ??= auth()->user();

        if (! $user || blank($workspaceId)) {
            return null;
        }

        /** @var WorkspaceUser|null $membership */
        $membership = $user->workspaceMemberships()
            ->with('role')
            ->where('workspace_id', $workspaceId)
            ->first();

        if (! $membership) {
            return null;
        }

        if ($membership->is_owner) {
            return 'owner';
        }

        return $membership->role?->slug;
    }

    public static function is(?string $workspaceId, string | array $roles, ?User $user = null): bool
    {
        $currentRole = static::current($workspaceId, $user);

        if (! $currentRole) {
            return false;
        }

        return in_array($currentRole, (array) $roles, true);
    }
}
