<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpdatePayrollSplitItemStatusRequest;
use App\Http\Requests\Finance\UpsertPayrollSplitRequest;
use App\Models\ProjectFinanceSplit;
use App\Models\ProjectFinanceSplitItem;
use App\Models\Workspace;
use App\Modules\Finance\Payroll\Queries\PayrollIndexQuery;
use App\Services\Finance\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class PayrollController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, PayrollIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Finance/Payroll/Index',
            title: 'Payroll & Fee',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(
        UpsertPayrollSplitRequest $request,
        Workspace $workspace,
        PayrollService $service
    ): RedirectResponse {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Payroll split created successfully.');
    }

    public function update(
        UpsertPayrollSplitRequest $request,
        Workspace $workspace,
        ProjectFinanceSplit $split,
        PayrollService $service
    ): RedirectResponse {
        $service->update($workspace, $split, $request->validated());

        return back()->with('success', 'Payroll split updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        ProjectFinanceSplit $split,
        PayrollService $service
    ): RedirectResponse {
        $service->delete($workspace, $split);

        return back()->with('success', 'Payroll split deleted successfully.');
    }

    public function updateItemStatus(
        UpdatePayrollSplitItemStatusRequest $request,
        Workspace $workspace,
        ProjectFinanceSplit $split,
        ProjectFinanceSplitItem $item,
        PayrollService $service
    ): RedirectResponse {
        $service->updateItemStatus($workspace, $split, $item, $request->validated()['status']);

        return back()->with('success', 'Payroll item status updated successfully.');
    }
}
