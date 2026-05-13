<?php

namespace App\Http\Controllers\App\Automation;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Automation\ToggleAutomationWorkflowRequest;
use App\Http\Requests\Automation\UpsertAutomationWorkflowRequest;
use App\Models\AutomationWorkflow;
use App\Models\Workspace;
use App\Modules\Automation\Queries\AutomationIndexQuery;
use App\Services\Automation\AutomationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AutomationController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, AutomationIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Automation/Index',
            title: 'Automation',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(
        UpsertAutomationWorkflowRequest $request,
        Workspace $workspace,
        AutomationWorkflowService $service
    ): RedirectResponse {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Automation workflow created successfully.');
    }

    public function update(
        UpsertAutomationWorkflowRequest $request,
        Workspace $workspace,
        AutomationWorkflow $workflow,
        AutomationWorkflowService $service
    ): RedirectResponse {
        $service->update($workspace, $workflow, $request->validated());

        return back()->with('success', 'Automation workflow updated successfully.');
    }

    public function toggle(
        ToggleAutomationWorkflowRequest $request,
        Workspace $workspace,
        AutomationWorkflow $workflow,
        AutomationWorkflowService $service
    ): RedirectResponse {
        $service->toggle($workspace, $workflow, $request->boolean('is_active'));

        return back()->with('success', 'Automation workflow status updated successfully.');
    }

    public function runTest(
        Workspace $workspace,
        AutomationWorkflow $workflow,
        AutomationWorkflowService $service
    ): RedirectResponse {
        $service->runTest($workspace, $workflow);

        return back()->with('success', 'Automation test run logged successfully.');
    }

    public function destroy(
        Workspace $workspace,
        AutomationWorkflow $workflow,
        AutomationWorkflowService $service
    ): RedirectResponse {
        $service->delete($workspace, $workflow);

        return back()->with('success', 'Automation workflow deleted successfully.');
    }
}
