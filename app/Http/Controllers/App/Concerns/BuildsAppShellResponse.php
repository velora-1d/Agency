<?php

namespace App\Http\Controllers\App\Concerns;

use App\Models\Workspace;
use App\Services\NavigationService;
use Inertia\Inertia;
use Inertia\Response;

trait BuildsAppShellResponse
{
    protected function appShell(
        Workspace $workspace,
        string $screen,
        string $title,
        array $payload = [],
        ?string $activeLabel = null,
    ): Response {
        $nav = app(NavigationService::class);

        return Inertia::render($screen, array_merge($payload, [
            'workspace' => $workspace->only(['id', 'name', 'slug']),
            'title' => $title,
            'navigation' => $nav->getNavigation($workspace, $activeLabel ?? $title),
        ]));
    }
}
