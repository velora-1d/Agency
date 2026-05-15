<?php

namespace App\Http\Controllers\App\System;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Modules\System\Executive\Queries\ExecutiveHubQuery;
use Illuminate\Http\Request;
use Inertia\Response;

class ExecutiveHubController extends Controller
{
    use BuildsAppShellResponse;

    /**
     * Menampilkan dashboard eksekutif lintas workspace.
     */
    public function index(Request $request, Workspace $workspace, ExecutiveHubQuery $query): Response
    {
        // Fitur ini hanya untuk Owner atau role khusus yang punya akses master
        $user = $request->user();
        
        // Cek apakah user adalah owner di workspace ini (sebagai proxy keamanan awal)
        $membership = $user->workspaceMemberships()->where('workspace_id', $workspace->id)->first();
        abort_unless($membership?->is_owner, 403, 'Akses Pusat Kendali hanya untuk Owner.');

        return $this->appShell(
            workspace: $workspace,
            screen: 'System/Executive/Hub',
            title: 'Pusat Kendali Owner',
            payload: $query->getHubPayload($user, $request->all()),
            activeLabel: 'Executive Hub',
        );
    }
}
