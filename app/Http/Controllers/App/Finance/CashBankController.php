<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\ReconcileBankAccountRequest;
use App\Http\Requests\Finance\TransferFundsRequest;
use App\Http\Requests\Finance\UpsertBankAccountRequest;
use App\Models\BankAccount;
use App\Models\Workspace;
use App\Modules\Finance\CashBank\Queries\CashBankIndexQuery;
use App\Services\Finance\CashBankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CashBankController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, CashBankIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Finance/CashBank/Index',
            title: 'Kas & Bank',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(
        UpsertBankAccountRequest $request,
        Workspace $workspace,
        CashBankService $service
    ): RedirectResponse {
        $service->createAccount($workspace, $request->validated());

        return back()->with('success', 'Account created successfully.');
    }

    public function update(
        UpsertBankAccountRequest $request,
        Workspace $workspace,
        BankAccount $account,
        CashBankService $service
    ): RedirectResponse {
        $service->updateAccount($workspace, $account, $request->validated());

        return back()->with('success', 'Account updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        BankAccount $account,
        CashBankService $service
    ): RedirectResponse {
        $service->deleteAccount($workspace, $account);

        return back()->with('success', 'Account deleted successfully.');
    }

    public function transfer(
        TransferFundsRequest $request,
        Workspace $workspace,
        CashBankService $service
    ): RedirectResponse {
        $service->transfer($workspace, $request->validated());

        return back()->with('success', 'Transfer recorded successfully.');
    }

    public function reconcile(
        ReconcileBankAccountRequest $request,
        Workspace $workspace,
        BankAccount $account,
        CashBankService $service
    ): RedirectResponse {
        $service->reconcile($workspace, $account, $request->validated());

        return back()->with('success', 'Account reconciled successfully.');
    }
}
