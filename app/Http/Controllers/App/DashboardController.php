<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Modules\Main\Dashboard\Queries\DashboardDataQuery;
use App\Services\NavigationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Workspace $workspace, DashboardDataQuery $dashboardDataQuery, NavigationService $nav): Response
    {
        return Inertia::render('Dashboard/Index', [
            'workspace' => $workspace->only(['id', 'name', 'slug']),
            'navigation' => $nav->getNavigation($workspace, 'Dashboard'),
            'dashboard' => $dashboardDataQuery->forWorkspace($workspace, Auth::user(), request()->all()),
        ]);
    }

}
