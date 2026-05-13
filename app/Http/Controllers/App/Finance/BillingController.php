<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertBillingRequest;
use App\Models\Billing;
use App\Models\Workspace;
use App\Modules\Finance\Billings\Queries\BillingIndexQuery;
use App\Services\Finance\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class BillingController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, BillingIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Finance/Billings/Index',
            title: 'Billing',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(
        UpsertBillingRequest $request,
        Workspace $workspace,
        BillingService $service
    ): RedirectResponse {
        $service->createBilling($workspace, $request->validated());

        return back()->with('success', 'Billing created successfully.');
    }

    public function update(
        UpsertBillingRequest $request,
        Workspace $workspace,
        Billing $billing,
        BillingService $service
    ): RedirectResponse {
        $service->updateBilling($workspace, $billing, $request->validated());

        return back()->with('success', 'Billing updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Billing $billing,
        BillingService $service
    ): RedirectResponse {
        $service->deleteBilling($workspace, $billing);

        return back()->with('success', 'Billing deleted successfully.');
    }

    public function generateInvoice(
        Workspace $workspace,
        Billing $billing,
        BillingService $service
    ): RedirectResponse {
        $invoice = $service->generateInvoice($workspace, $billing);

        return back()->with('success', sprintf('Invoice %s generated successfully.', $invoice->number));
    }

    public function generateDue(
        Workspace $workspace,
        BillingService $service
    ): RedirectResponse {
        $count = $service->generateDueInvoices($workspace);

        return back()->with('success', sprintf('%d due invoice(s) generated successfully.', $count));
    }
}
