<?php

namespace App\Http\Controllers\App\Automation;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Modules\Automation\ApiKeys\Queries\ApiKeyIndexQuery;
use Inertia\Response;

class ApiKeyController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Workspace $workspace, ApiKeyIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Automation/ApiKeys/Index',
            title: 'API Keys',
            payload: $query->getIndexPayload($workspace),
        );
    }
}
