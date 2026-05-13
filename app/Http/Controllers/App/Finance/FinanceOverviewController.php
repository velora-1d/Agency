<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Overview\Queries\FinanceOverviewQuery;
use Illuminate\Http\Request;
use App\Models\Workspace;
use Inertia\Response;

class FinanceOverviewController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, FinanceOverviewQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Finance/Overview',
            title: 'Finance Overview',
            payload: $query->getOverviewPayload($workspace, $request->all()),
        );
    }
}
