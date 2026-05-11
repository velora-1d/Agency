<?php

namespace App\Http\Controllers\App\Communication;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Inertia\Response;

class InboxController extends Controller
{
    use BuildsAppShellResponse;

    public function __invoke(Workspace $workspace): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Communication/Inbox',
            title: 'Unified Inbox',
        );
    }
}
