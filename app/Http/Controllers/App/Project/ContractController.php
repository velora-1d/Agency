<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Workspace;
use App\Modules\Project\Contracts\Queries\ContractIndexQuery;
use App\Services\Project\ContractService;
use App\Services\Communication\EvolutionApiService;
use Barryvdh\DomPDF\Facade\Pdf;
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
            activeLabel: 'Projects',
        );
    }

    public function previewPdf(Workspace $workspace, Contract $contract)
    {
        abort_unless($contract->workspace_id === $workspace->id, 404);

        $contract->load(['client', 'project', 'quotation.items']);

        $pdf = Pdf::loadView('pdf.contract', [
            'workspace' => $workspace,
            'contract' => $contract,
        ]);

        return $pdf->stream("Contract-{$contract->title}.pdf");
    }

    public function store(Request $request, Workspace $workspace, ContractService $service): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['nullable', 'uuid', 'exists:clients,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'quotation_id' => ['nullable', 'uuid', 'exists:quotations,id'],
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
            'quotation_id' => ['nullable', 'uuid', 'exists:quotations,id'],
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
        $contract->load(['client', 'project', 'quotation.items']);

        return $this->appShell(
            workspace: $workspace,
            screen: 'Projects/Contracts/Show',
            title: 'Contract Detail',
            payload: $query->getShowPayload($workspace, $contract),
            activeLabel: 'Projects',
        );
    }

    public function sendWhatsApp(Workspace $workspace, Contract $contract, EvolutionApiService $wa): RedirectResponse
    {
        abort_unless($contract->workspace_id === $workspace->id, 404);
        
        $client = $contract->client;
        if (!$client || !$client->phone) {
            return back()->with('error', 'Nomor WhatsApp klien tidak ditemukan.');
        }

        $message = "Halo *{$client->pic_name}*,\n\nBerikut draf kontrak untuk proyek *{$contract->title}*.\nSilakan tinjau dan informasikan jika ada perubahan.\n\nSalam,\n*{$workspace->name}*";
        
        $waInstance = data_get($workspace->settings, 'wa_instance', 'default');
        $success = $wa->sendMessage($waInstance, $client->phone, $message);

        if ($success) {
            $contract->update(['status' => 'sent']);
            return back()->with('success', 'Kontrak berhasil dikirim via WhatsApp.');
        }

        return back()->with('error', 'Gagal mengirim pesan via WhatsApp.');
    }
}
