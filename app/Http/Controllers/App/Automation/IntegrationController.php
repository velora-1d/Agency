<?php

namespace App\Http\Controllers\App\Automation;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Modules\Automation\Integrations\Queries\IntegrationIndexQuery;
use Inertia\Response;

class IntegrationController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Workspace $workspace, IntegrationIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Automation/Integrations/Index',
            title: 'Integrations',
            payload: $query->getIndexPayload($workspace),
        );
    }
}
