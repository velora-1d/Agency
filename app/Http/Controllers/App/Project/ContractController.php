<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Workspace;
use App\Modules\Project\Contracts\Queries\ContractIndexQuery;
use App\Services\Project\ContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ContractController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, ContractIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Projects/Contracts/Index',
            title: 'Contracts',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(Request $request, Workspace $workspace, ContractService $service): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['nullable', 'uuid', 'exists:clients,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'reminder_days_before' => ['nullable', 'integer', 'min:1'],
        ]);

        $service->create($workspace, $validated);

        return back()->with('success', 'Contract created successfully as Draft.');
    }

    public function update(Request $request, Workspace $workspace, Contract $contract, ContractService $service): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['nullable', 'uuid', 'exists:clients,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'reminder_days_before' => ['nullable', 'integer', 'min:1'],
        ]);

        $service->update($workspace, $contract, $validated);

        return back()->with('success', 'Contract updated successfully.');
    }

    public function updateStatus(Request $request, Workspace $workspace, Contract $contract, ContractService $service): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,sent,signed,expired'],
        ]);

        $service->updateStatus($workspace, $contract, $validated['status']);

        return back()->with('success', 'Contract status updated successfully.');
    }

    public function uploadSigned(Request $request, Workspace $workspace, Contract $contract, ContractService $service): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // Max 10MB PDF
        ]);

        $service->uploadSignedDocument($workspace, $contract, $request->file('file'));

        return back()->with('success', 'Signed contract document uploaded successfully.');
    }

    public function destroy(Workspace $workspace, Contract $contract, ContractService $service): RedirectResponse
    {
        $service->delete($workspace, $contract);

        return redirect()->route('workspace.projects.contracts.index', $workspace)
            ->with('success', 'Contract deleted successfully.');
    }

    public function show(Workspace $workspace, Contract $contract, ContractIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Projects/Contracts/Show',
            title: 'Contract Detail',
            payload: $query->getShowPayload($workspace, $contract),
        );
    }
}
