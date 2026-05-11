<?php

namespace App\Http\Controllers\App\Communication;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use App\Models\Workspace;
use App\Modules\Communication\Calendar\Queries\CalendarQuery;
use App\Services\Communication\CalendarService;
use Illuminate\Http\Request;
use Inertia\Response;

class CalendarController extends Controller
{
    protected CalendarService $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, CalendarQuery $query): Response
    {
        $start = $request->input('start', now()->startOfMonth()->subDays(7)->toDateString());
        $end = $request->input('end', now()->endOfMonth()->addDays(7)->toDateString());

        $events = $query->getEvents($workspace, $start, $end);

        return $this->appShell(
            workspace: $workspace,
            screen: 'Communication/Calendar',
            title: 'Calendar',
            payload: [
                'initialEvents' => CalendarEventResource::collection($events)->resolve(),
                'filters' => $request->only(['start', 'end', 'type']),
            ]
        );
    }

    public function store(StoreCalendarEventRequest $request, Workspace $workspace): \Illuminate\Http\RedirectResponse
    {
        $this->calendarService->createEvent($workspace, $request->validated());

        return back()->with('success', 'Event created successfully.');
    }

    public function update(StoreCalendarEventRequest $request, Workspace $workspace, CalendarEvent $calendarEvent): \Illuminate\Http\RedirectResponse
    {
        $calendarEvent->update($request->validated());

        return back()->with('success', 'Event updated successfully.');
    }

    public function destroy(Workspace $workspace, CalendarEvent $calendarEvent): \Illuminate\Http\RedirectResponse
    {
        $this->calendarService->deleteEvent($workspace, $calendarEvent);

        return back()->with('success', 'Event deleted successfully.');
    }
}
