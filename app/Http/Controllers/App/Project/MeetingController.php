<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\UpsertMeetingRequest;
use App\Models\Meeting;
use App\Models\Workspace;
use App\Modules\Project\Meetings\Queries\MeetingIndexQuery;
use App\Services\Project\MeetingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class MeetingController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, MeetingIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Meetings/Index',
            title: 'Meetings',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(UpsertMeetingRequest $request, Workspace $workspace, MeetingService $service): RedirectResponse
    {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Meeting created successfully.');
    }

    public function update(
        UpsertMeetingRequest $request,
        Workspace $workspace,
        Meeting $meeting,
        MeetingService $service
    ): RedirectResponse {
        $service->update($workspace, $meeting, $request->validated());

        return back()->with('success', 'Meeting updated successfully.');
    }

    public function destroy(Workspace $workspace, Meeting $meeting, MeetingService $service): RedirectResponse
    {
        $service->delete($workspace, $meeting);

        return back()->with('success', 'Meeting deleted successfully.');
    }
}
