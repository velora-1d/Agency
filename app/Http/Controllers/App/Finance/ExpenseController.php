<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpdateReimbursementStatusRequest;
use App\Http\Requests\Finance\UpsertDepartmentBudgetRequest;
use App\Http\Requests\Finance\UpsertReimbursementRequest;
use App\Models\DepartmentBudget;
use App\Models\Reimbursement;
use App\Models\Workspace;
use App\Modules\Finance\Expenses\Queries\ExpenseIndexQuery;
use App\Services\Finance\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ExpenseController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, ExpenseIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Finance/Expenses/Index',
            title: 'Expense & Reimbursement',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function storeReimbursement(
        UpsertReimbursementRequest $request,
        Workspace $workspace,
        ExpenseService $service
    ): RedirectResponse {
        $service->createReimbursement($workspace, $request->validated());

        return back()->with('success', 'Reimbursement created successfully.');
    }

    public function updateReimbursement(
        UpsertReimbursementRequest $request,
        Workspace $workspace,
        Reimbursement $reimbursement,
        ExpenseService $service
    ): RedirectResponse {
        $service->updateReimbursement($workspace, $reimbursement, $request->validated());

        return back()->with('success', 'Reimbursement updated successfully.');
    }

    public function destroyReimbursement(
        Workspace $workspace,
        Reimbursement $reimbursement,
        ExpenseService $service
    ): RedirectResponse {
        $service->deleteReimbursement($workspace, $reimbursement);

        return back()->with('success', 'Reimbursement deleted successfully.');
    }

    public function updateReimbursementStatus(
        UpdateReimbursementStatusRequest $request,
        Workspace $workspace,
        Reimbursement $reimbursement,
        ExpenseService $service
    ): RedirectResponse {
        $service->updateStatus($workspace, $reimbursement, $request->validated()['status'], $request->validated()['paid_account_id'] ?? null);

        return back()->with('success', 'Reimbursement status updated successfully.');
    }

    public function storeBudget(
        UpsertDepartmentBudgetRequest $request,
        Workspace $workspace,
        ExpenseService $service
    ): RedirectResponse {
        $service->createBudget($workspace, $request->validated());

        return back()->with('success', 'Budget created successfully.');
    }

    public function updateBudget(
        UpsertDepartmentBudgetRequest $request,
        Workspace $workspace,
        DepartmentBudget $budget,
        ExpenseService $service
    ): RedirectResponse {
        $service->updateBudget($workspace, $budget, $request->validated());

        return back()->with('success', 'Budget updated successfully.');
    }

    public function destroyBudget(
        Workspace $workspace,
        DepartmentBudget $budget,
        ExpenseService $service
    ): RedirectResponse {
        $service->deleteBudget($workspace, $budget);

        return back()->with('success', 'Budget deleted successfully.');
    }
}
