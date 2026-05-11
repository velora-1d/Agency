<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->workspaceMemberships()->exists();
    }

    public function view(User $user, Workspace $workspace): bool
    {
        return $user->canAccessTenant($workspace);
    }

    public function update(User $user, Workspace $workspace): bool
    {
        return $user->canInWorkspace('settings', 'edit', $workspace->getKey())
            || $user->canInWorkspace('workspaces', 'edit', $workspace->getKey());
    }
}
