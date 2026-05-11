<?php

namespace App\Http\Controllers\App\Communication;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\UpsertSupportTicketRequest;
use App\Models\Client;
use App\Models\SupportTicket;
use App\Models\Workspace;
use App\Modules\Communication\SupportTickets\Queries\SupportTicketIndexQuery;
use App\Services\Communication\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class SupportTicketController extends Controller
{
    use BuildsAppShellResponse;

    public function __construct(
        protected SupportTicketService $service
    ) {}

    public function index(Request $request, Workspace $workspace, SupportTicketIndexQuery $query): Response
    {
        $payload = $query->getIndexPayload($workspace, $request->all());
        
        $clients = Client::query()
            ->where('workspace_id', $workspace->id)
            ->get(['id', 'company_name']);

        $team = $workspace->users()->get(['users.id', 'users.name']);

        return $this->appShell(
            workspace: $workspace,
            screen: 'Communication/SupportTickets/Index',
            title: 'Support Tickets',
            payload: array_merge($payload, [
                'clients' => $clients,
                'team' => $team,
            ]),
        );
    }

    public function store(UpsertSupportTicketRequest $request, Workspace $workspace): RedirectResponse
    {
        $this->service->create($workspace, $request->validated());

        return back()->with('success', 'Support ticket created successfully.');
    }

    public function update(UpsertSupportTicketRequest $request, Workspace $workspace, SupportTicket $supportTicket): RedirectResponse
    {
        $this->service->update($workspace, $supportTicket, $request->validated());

        return back()->with('success', 'Support ticket updated successfully.');
    }

    public function destroy(Workspace $workspace, SupportTicket $supportTicket): RedirectResponse
    {
        $this->service->delete($workspace, $supportTicket);

        return back()->with('success', 'Support ticket deleted successfully.');
    }
}
