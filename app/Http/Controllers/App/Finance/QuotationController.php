<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\ConvertQuotationRequest;
use App\Http\Requests\Finance\UpdateQuotationStatusRequest;
use App\Http\Requests\Finance\UpsertQuotationRequest;
use App\Models\Quotation;
use App\Models\Workspace;
use App\Modules\Finance\Quotations\Queries\QuotationIndexQuery;
use App\Services\Finance\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class QuotationController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, QuotationIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Finance/Quotations/Index',
            title: 'Quotation',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(
        UpsertQuotationRequest $request,
        Workspace $workspace,
        QuotationService $service
    ): RedirectResponse {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Quotation created successfully.');
    }

    public function update(
        UpsertQuotationRequest $request,
        Workspace $workspace,
        Quotation $quotation,
        QuotationService $service
    ): RedirectResponse {
        $service->update($workspace, $quotation, $request->validated());

        return back()->with('success', 'Quotation updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Quotation $quotation,
        QuotationService $service
    ): RedirectResponse {
        $service->delete($workspace, $quotation);

        return back()->with('success', 'Quotation deleted successfully.');
    }

    public function updateStatus(
        UpdateQuotationStatusRequest $request,
        Workspace $workspace,
        Quotation $quotation,
        QuotationService $service
    ): RedirectResponse {
        $service->updateStatus($workspace, $quotation, $request->validated()['status']);

        return back()->with('success', 'Quotation status updated successfully.');
    }

    public function send(
        Workspace $workspace,
        Quotation $quotation,
        QuotationService $service
    ): RedirectResponse {
        $service->send($workspace, $quotation);

        return back()->with('success', 'Quotation sent successfully.');
    }

    public function convert(
        ConvertQuotationRequest $request,
        Workspace $workspace,
        Quotation $quotation,
        QuotationService $service
    ): RedirectResponse {
        $service->convertToInvoiceAndProject(
            $workspace,
            $quotation,
            (bool) ($request->validated()['create_project'] ?? true),
        );

        return back()->with('success', 'Quotation converted successfully.');
    }
}
