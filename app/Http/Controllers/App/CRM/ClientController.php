<?php

namespace App\Http\Controllers\App\CRM;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientActivityRequest;
use App\Http\Requests\UpdateClientNotesRequest;
use App\Http\Requests\UpsertClientRequest;
use App\Models\Client;
use App\Models\Workspace;
use App\Modules\CRM\Clients\Queries\ClientIndexQuery;
use App\Services\CRM\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ClientController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, ClientIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'CRM/Clients/Index',
            title: 'Clients',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(UpsertClientRequest $request, Workspace $workspace, ClientService $service): RedirectResponse
    {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Client created successfully.');
    }

    public function update(UpsertClientRequest $request, Workspace $workspace, Client $client, ClientService $service): RedirectResponse
    {
        $service->update($workspace, $client, $request->validated());

        return back()->with('success', 'Client updated successfully.');
    }

    public function updateNotes(UpdateClientNotesRequest $request, Workspace $workspace, Client $client, ClientService $service): RedirectResponse
    {
        $service->updateNotes($workspace, $client, $request->validated('notes'));

        return back()->with('success', 'Client notes updated successfully.');
    }

    public function storeActivity(StoreClientActivityRequest $request, Workspace $workspace, Client $client, ClientService $service): RedirectResponse
    {
        $service->addActivity($workspace, $client, $request->validated('content'));

        return back()->with('success', 'Client activity added successfully.');
    }

    public function destroy(Workspace $workspace, Client $client, ClientService $service): RedirectResponse
    {
        $service->delete($workspace, $client);

        return back()->with('success', 'Client deleted successfully.');
    }

    public function show(Workspace $workspace, Client $client, ClientIndexQuery $query): Response
    {
        abort_unless($client->workspace_id === $workspace->getKey(), 404);

        $payload = $query->getShowPayload($workspace, $client);

        return $this->appShell(
            workspace: $workspace,
            screen: 'CRM/Clients/Show',
            title: 'Clients',
            payload: [
                ...$payload,
            ],
        );
    }
}
