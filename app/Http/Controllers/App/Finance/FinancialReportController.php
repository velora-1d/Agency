<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertChartOfAccountRequest;
use App\Models\ChartOfAccount;
use App\Models\Workspace;
use App\Modules\Finance\Reports\Queries\FinancialReportIndexQuery;
use App\Services\Finance\FinancialReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class FinancialReportController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, FinancialReportIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Finance/Reports/Index',
            title: 'Laporan Keuangan',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function storeChart(
        UpsertChartOfAccountRequest $request,
        Workspace $workspace,
        FinancialReportService $service
    ): RedirectResponse {
        $service->createChartOfAccount($workspace, $request->validated());

        return back()->with('success', 'Chart of account created successfully.');
    }

    public function updateChart(
        UpsertChartOfAccountRequest $request,
        Workspace $workspace,
        ChartOfAccount $account,
        FinancialReportService $service
    ): RedirectResponse {
        $service->updateChartOfAccount($workspace, $account, $request->validated());

        return back()->with('success', 'Chart of account updated successfully.');
    }

    public function destroyChart(
        Workspace $workspace,
        ChartOfAccount $account,
        FinancialReportService $service
    ): RedirectResponse {
        $service->deleteChartOfAccount($workspace, $account);

        return back()->with('success', 'Chart of account deleted successfully.');
    }
}
