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
use App\Services\Communication\EvolutionApiService;
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

    public function sendWhatsApp(Workspace $workspace, SupportTicket $supportTicket, EvolutionApiService $wa): RedirectResponse
    {
        abort_unless($supportTicket->workspace_id === $workspace->id, 404);

        $client = $supportTicket->client;
        if (!$client || !$client->phone) {
            return back()->with('error', 'Nomor WhatsApp klien tidak ditemukan.');
        }

        $statusLabel = match ($supportTicket->status) {
            'open' => 'Terbuka',
            'in_progress' => 'Sedang Diproses',
            'resolved' => 'Selesai',
            'closed' => 'Ditutup',
            default => $supportTicket->status
        };

        $message = "Halo *{$client->pic_name}*,\n\nTiket dukungan Anda *#{$supportTicket->id}* (*{$supportTicket->title}*) saat ini berstatus: *{$statusLabel}*.\n\nKami akan terus memberikan update terbaru. Terima kasih.\n\nSalam,\n*{$workspace->name}*";

        $success = $wa->sendMessage('default', $client->phone, $message);

        if ($success) {
            return back()->with('success', 'Update tiket berhasil dikirim via WhatsApp.');
        }

        return back()->with('error', 'Gagal mengirim update via WhatsApp.');
    }
}
