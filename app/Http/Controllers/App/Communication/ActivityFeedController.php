<?php

namespace App\Http\Controllers\App\Communication;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityFeedResource;
use App\Models\ActivityFeed;
use App\Models\Workspace;
use App\Modules\Communication\ActivityFeed\Queries\ActivityFeedQuery;
use App\Modules\Communication\ActivityFeed\Services\ActivityFeedService;
use App\Http\Requests\StoreActivityCommentRequest;
use App\Services\NavigationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ActivityFeedController extends Controller
{
    public function __invoke(Request $request, Workspace $workspace, ActivityFeedQuery $query, NavigationService $nav): Response
    {
        $activities = $query->forWorkspace($workspace, $request->only(['type', 'user_id', 'date_from', 'date_to']));
        
        return Inertia::render('Communication/ActivityFeed', [
            'workspace' => $workspace->only(['id', 'name', 'slug']),
            'navigation' => $nav->getNavigation($workspace, 'Activity Feed'),
            'filters' => [
                'options' => $query->getFilterOptions($workspace),
                'values' => $request->only(['type', 'user_id', 'date_from', 'date_to']),
            ],
            'activities' => ActivityFeedResource::collection($activities),
        ]);
    }

    public function storeComment(StoreActivityCommentRequest $request, Workspace $workspace, ActivityFeed $activity, ActivityFeedService $service): RedirectResponse
    {
        $service->addComment($activity, $request->validated());

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }
}
