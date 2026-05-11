<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Workspace;
use Inertia\Response;

class ProjectController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Workspace $workspace): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Projects/Index',
            title: 'Projects',
        );
    }

    public function show(Workspace $workspace, Project $project): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Projects/Show/Overview',
            title: 'Project Workspace',
            payload: [
                'projectId' => $project->getKey(),
            ],
        );
    }
}
