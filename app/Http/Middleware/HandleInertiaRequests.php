<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'inertia';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $workspace = $request->route('workspace'); 

        // Resolusi Workspace ID (bisa berupa object atau string slug)
        $workspaceId = null;
        if (is_object($workspace)) {
            $workspaceId = $workspace->id;
        } elseif (is_string($workspace)) {
            // Cache atau cari ID berdasarkan slug jika rute belum ter-bind
            $workspaceId = \App\Models\Workspace::where('slug', $workspace)->value('id');
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ] : null,
                'workspaces' => $user ? $user->workspaces()->get(['workspaces.id', 'workspaces.name', 'workspaces.slug']) : [],
                'current_membership' => ($user && $workspaceId) ? $user->workspaceMemberships()
                    ->where('workspace_id', $workspaceId)
                    ->with('role:id,name,slug')
                    ->first() : null,
            ],
        ];
    }
}
