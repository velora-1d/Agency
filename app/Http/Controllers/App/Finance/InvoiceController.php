<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreInvoicePaymentRequest;
use App\Http\Requests\Finance\UpdateInvoiceStatusRequest;
use App\Http\Requests\Finance\UpsertInvoiceRequest;
use App\Models\Invoice;
use App\Models\Workspace;
use App\Modules\Finance\Invoices\Queries\InvoiceIndexQuery;
use App\Services\Finance\InvoiceService;
use App\Integrations\Pakasir\PakasirService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class InvoiceController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, InvoiceIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Finance/Invoices/Index',
            title: 'Invoices',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(
        UpsertInvoiceRequest $request,
        Workspace $workspace,
        InvoiceService $service
    ): RedirectResponse {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Invoice created successfully.');
    }

    public function update(
        UpsertInvoiceRequest $request,
        Workspace $workspace,
        Invoice $invoice,
        InvoiceService $service
    ): RedirectResponse {
        $service->update($workspace, $invoice, $request->validated());

        return back()->with('success', 'Invoice updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Invoice $invoice,
        InvoiceService $service
    ): RedirectResponse {
        $service->delete($workspace, $invoice);

        return back()->with('success', 'Invoice deleted successfully.');
    }

    public function updateStatus(
        UpdateInvoiceStatusRequest $request,
        Workspace $workspace,
        Invoice $invoice,
        InvoiceService $service
    ): RedirectResponse {
        $service->updateStatus($workspace, $invoice, $request->validated()['status']);

        return back()->with('success', 'Invoice status updated successfully.');
    }

    public function approve(
        Workspace $workspace,
        Invoice $invoice,
        InvoiceService $service
    ): RedirectResponse {
        $service->approve($workspace, $invoice);

        return back()->with('success', 'Invoice approved for sending.');
    }

    public function recordPayment(
        StoreInvoicePaymentRequest $request,
        Workspace $workspace,
        Invoice $invoice,
        InvoiceService $service
    ): RedirectResponse {
        $service->recordPayment($workspace, $invoice, $request->validated());

        return back()->with('success', 'Payment confirmation recorded successfully.');
    }

    public function generatePakasirLink(
        Workspace $workspace,
        Invoice $invoice,
        PakasirService $pakasir
    ): RedirectResponse {
        abort_unless($invoice->workspace_id === $workspace->id, 404);

        $result = $pakasir->createPaymentLink($invoice);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        $invoice->update([
            'pakasir_order_id' => $result['order_id'],
            'pakasir_payment_url' => $result['payment_url'],
        ]);

        return back()->with('success', 'Link pembayaran Pakasir berhasil dibuat.');
    }
}
