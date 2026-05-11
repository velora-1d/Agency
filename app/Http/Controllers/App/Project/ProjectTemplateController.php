<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\UpsertProjectTemplateRequest;
use App\Models\ProjectTemplate;
use App\Models\Workspace;
use App\Services\Project\ProjectService;
use Illuminate\Http\RedirectResponse;

class ProjectTemplateController extends Controller
{
    public function store(
        UpsertProjectTemplateRequest $request,
        Workspace $workspace,
        ProjectService $service
    ): RedirectResponse {
        $service->createTemplate($workspace, $request->validated());

        return back()->with('success', 'Project template created successfully.');
    }

    public function update(
        UpsertProjectTemplateRequest $request,
        Workspace $workspace,
        ProjectTemplate $template,
        ProjectService $service
    ): RedirectResponse {
        $service->updateTemplate($workspace, $template, $request->validated());

        return back()->with('success', 'Project template updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        ProjectTemplate $template,
        ProjectService $service
    ): RedirectResponse {
        $service->deleteTemplate($workspace, $template);

        return back()->with('success', 'Project template deleted successfully.');
    }
}
