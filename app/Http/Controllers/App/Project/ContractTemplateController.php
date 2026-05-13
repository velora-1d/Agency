<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\ContractTemplate;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContractTemplateController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Workspace $workspace)
    {
        $templates = ContractTemplate::query()
            ->where('workspace_id', $workspace->getKey())
            ->latest()
            ->get();

        return $this->appShell(
            workspace: $workspace,
            screen: 'Projects/Contracts/Templates',
            title: 'Contract Templates',
            payload: [
                'templates' => $templates,
            ],
            activeLabel: 'Projects',
        );
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        ContractTemplate::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $validated['name'],
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Template created successfully.');
    }

    public function update(Request $request, Workspace $workspace, ContractTemplate $template): RedirectResponse
    {
        abort_unless($template->workspace_id === $workspace->getKey(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $template->update($validated);

        return back()->with('success', 'Template updated successfully.');
    }

    public function destroy(Workspace $workspace, ContractTemplate $template): RedirectResponse
    {
        abort_unless($template->workspace_id === $workspace->getKey(), 404);

        $template->delete();

        return back()->with('success', 'Template deleted successfully.');
    }
}
