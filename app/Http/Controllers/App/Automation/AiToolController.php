<?php

namespace App\Http\Controllers\App\Automation;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Modules\Automation\AiTools\Queries\AiToolIndexQuery;
use Inertia\Response;

class AiToolController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Workspace $workspace, AiToolIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Automation/AiTools/Index',
            title: 'AI Tools',
            payload: $query->getIndexPayload($workspace),
        );
    }
}
