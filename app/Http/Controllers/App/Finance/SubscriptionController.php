<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpdateSubscriptionStatusRequest;
use App\Http\Requests\Finance\UpsertSubscriptionRequest;
use App\Http\Requests\Finance\UpsertVendorRequest;
use App\Models\Subscription;
use App\Models\Vendor;
use App\Models\Workspace;
use App\Modules\Finance\Subscriptions\Queries\SubscriptionIndexQuery;
use App\Services\Finance\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class SubscriptionController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, SubscriptionIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Finance/Subscriptions/Index',
            title: 'Subscriptions',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(
        UpsertSubscriptionRequest $request,
        Workspace $workspace,
        SubscriptionService $service
    ): RedirectResponse {
        $service->createSubscription($workspace, $request->validated());

        return back()->with('success', 'Subscription created successfully.');
    }

    public function update(
        UpsertSubscriptionRequest $request,
        Workspace $workspace,
        Subscription $subscription,
        SubscriptionService $service
    ): RedirectResponse {
        $service->updateSubscription($workspace, $subscription, $request->validated());

        return back()->with('success', 'Subscription updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Subscription $subscription,
        SubscriptionService $service
    ): RedirectResponse {
        $service->deleteSubscription($workspace, $subscription);

        return back()->with('success', 'Subscription deleted successfully.');
    }

    public function updateStatus(
        UpdateSubscriptionStatusRequest $request,
        Workspace $workspace,
        Subscription $subscription,
        SubscriptionService $service
    ): RedirectResponse {
        $service->updateSubscriptionStatus($workspace, $subscription, $request->validated()['status']);

        return back()->with('success', 'Subscription status updated successfully.');
    }

    public function storeVendor(
        UpsertVendorRequest $request,
        Workspace $workspace,
        SubscriptionService $service
    ): RedirectResponse {
        $service->createVendor($workspace, $request->validated());

        return back()->with('success', 'Vendor created successfully.');
    }

    public function updateVendor(
        UpsertVendorRequest $request,
        Workspace $workspace,
        Vendor $vendor,
        SubscriptionService $service
    ): RedirectResponse {
        $service->updateVendor($workspace, $vendor, $request->validated());

        return back()->with('success', 'Vendor updated successfully.');
    }

    public function destroyVendor(
        Workspace $workspace,
        Vendor $vendor,
        SubscriptionService $service
    ): RedirectResponse {
        $service->deleteVendor($workspace, $vendor);

        return back()->with('success', 'Vendor deleted successfully.');
    }
}
