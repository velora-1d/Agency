<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\UpdateProjectStatusRequest;
use App\Http\Requests\Project\UpsertProjectRequest;
use App\Models\Project;
use App\Models\Workspace;
use App\Modules\Project\Projects\Queries\ProjectIndexQuery;
use App\Services\Project\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ProjectController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, ProjectIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Projects/Index',
            title: 'Projects',
            payload: $query->getIndexPayload($workspace, $request->all()),
            activeLabel: 'Projects',
        );
    }

    public function store(UpsertProjectRequest $request, Workspace $workspace, ProjectService $service): RedirectResponse
    {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Project created successfully.');
    }

    public function update(
        UpsertProjectRequest $request,
        Workspace $workspace,
        Project $project,
        ProjectService $service
    ): RedirectResponse {
        $service->update($workspace, $project, $request->validated());

        return back()->with('success', 'Project updated successfully.');
    }

    public function destroy(Workspace $workspace, Project $project, ProjectService $service): RedirectResponse
    {
        $service->delete($workspace, $project);

        return back()->with('success', 'Project deleted successfully.');
    }

    public function updateStatus(
        UpdateProjectStatusRequest $request,
        Workspace $workspace,
        Project $project,
        ProjectService $service
    ): RedirectResponse {
        $service->updateStatus($workspace, $project, $request->validated('status'));

        return back()->with('success', 'Project status updated successfully.');
    }

    public function show(Workspace $workspace, Project $project, ProjectIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Projects/Show/Overview',
            title: 'Projects',
            payload: $query->getShowPayload($workspace, $project),
            activeLabel: 'Projects',
        );
    }
}
