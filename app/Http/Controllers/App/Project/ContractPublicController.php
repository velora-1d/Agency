<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\Project\ContractService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractPublicController extends Controller
{
    public function showEsign(string $token): Response
    {
        $contract = Contract::query()
            ->where('esign_token', $token)
            ->with(['client:id,company_name', 'project:id,name', 'workspace:id,name,logo'])
            ->firstOrFail();

        abort_if($contract->status === 'signed', 403, 'Kontrak ini sudah ditandatangani.');
        abort_if($contract->status === 'expired', 403, 'Masa berlaku kontrak ini sudah habis.');

        return Inertia::render('Projects/Contracts/PublicEsign', [
            'contract' => [
                'id' => $contract->id,
                'title' => $contract->title,
                'content' => $contract->content,
                'client_name' => $contract->client?->company_name,
                'project_name' => $contract->project?->name,
                'workspace_name' => $contract->workspace?->name,
                'workspace_logo' => $contract->workspace?->logo,
            ],
            'token' => $token,
        ]);
    }

    public function sign(Request $request, string $token, ContractService $service)
    {
        $contract = Contract::query()
            ->where('esign_token', $token)
            ->firstOrFail();

        abort_if($contract->status === 'signed', 403, 'Kontrak ini sudah ditandatangani.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'agreement' => ['required', 'accepted'],
        ]);

        $service->signDigitally($contract, $validated['name'], $request->ip());

        return Inertia::render('Projects/Contracts/PublicEsignSuccess', [
            'contract_title' => $contract->title,
            'signed_at' => now()->format('d M Y H:i'),
        ]);
    }
}
