<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetWorkspaceContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspace = $request->route('workspace');

        if ($workspace instanceof Workspace) {
            WorkspaceContext::set($workspace);
        } else {
            WorkspaceContext::clear();
        }

        return $next($request);
    }
}
