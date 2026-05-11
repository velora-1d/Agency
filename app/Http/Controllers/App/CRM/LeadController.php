<?php

namespace App\Http\Controllers\App\CRM;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\MoveLeadStageRequest;
use App\Http\Requests\StoreLeadActivityRequest;
use App\Http\Requests\UpdateLeadAutomationRequest;
use App\Http\Requests\UpdateLeadNotesRequest;
use App\Http\Requests\UpsertPipelineRequest;
use App\Http\Requests\UpsertLeadRequest;
use App\Http\Resources\LeadActivityResource;
use App\Models\ActivityFeed;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Workspace;
use App\Modules\CRM\Leads\Queries\LeadIndexQuery;
use App\Services\CRM\LeadService;
use App\Services\CRM\LeadAutomationService;
use App\Services\CRM\PipelineService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Response;

class LeadController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, LeadIndexQuery $query): Response
    {
        $payload = $query->getIndexPayload($workspace, $request->all());

        return $this->appShell(
            workspace: $workspace,
            screen: 'CRM/Leads/Pipeline',
            title: 'Leads',
            payload: $payload,
        );
    }

    public function store(UpsertLeadRequest $request, Workspace $workspace, LeadService $service): RedirectResponse
    {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Lead created successfully.');
    }

    public function update(UpsertLeadRequest $request, Workspace $workspace, Lead $lead, LeadService $service): RedirectResponse
    {
        $service->update($workspace, $lead, $request->validated());

        return back()->with('success', 'Lead updated successfully.');
    }

    public function moveStage(MoveLeadStageRequest $request, Workspace $workspace, Lead $lead, LeadService $service): RedirectResponse
    {
        $service->moveStage(
            $workspace,
            $lead,
            $request->validated('pipeline_id'),
            $request->validated('stage_id'),
        );

        return back()->with('success', 'Lead stage updated successfully.');
    }

    public function storePipeline(UpsertPipelineRequest $request, Workspace $workspace, PipelineService $service): RedirectResponse
    {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Pipeline created successfully.');
    }

    public function updatePipeline(UpsertPipelineRequest $request, Workspace $workspace, Pipeline $pipeline, PipelineService $service): RedirectResponse
    {
        $service->update($workspace, $pipeline, $request->validated());

        return back()->with('success', 'Pipeline updated successfully.');
    }

    public function updateAutomation(UpdateLeadAutomationRequest $request, Workspace $workspace, LeadAutomationService $service): RedirectResponse
    {
        $service->update($workspace, $request->validated());

        return back()->with('success', 'Lead automation updated successfully.');
    }

    public function updateNotes(UpdateLeadNotesRequest $request, Workspace $workspace, Lead $lead, LeadService $service): RedirectResponse
    {
        $service->updateNotes($workspace, $lead, $request->validated('notes'));

        return back()->with('success', 'Lead notes updated successfully.');
    }

    public function storeActivity(StoreLeadActivityRequest $request, Workspace $workspace, Lead $lead, LeadService $service): RedirectResponse
    {
        $service->addActivity($workspace, $lead, $request->validated('content'));

        return back()->with('success', 'Lead activity added successfully.');
    }

    public function convertToClient(Workspace $workspace, Lead $lead, LeadService $service): RedirectResponse
    {
        $client = $service->convertToClient($workspace, $lead);

        return redirect()->route('workspace.crm.clients.index', $workspace)
            ->with('success', sprintf('Lead berhasil dikonversi menjadi client: %s', $client->company_name));
    }

    public function destroyPipeline(Workspace $workspace, Pipeline $pipeline, PipelineService $service): RedirectResponse
    {
        $service->delete($workspace, $pipeline);

        return back()->with('success', 'Pipeline deleted successfully.');
    }

    public function export(Request $request, Workspace $workspace, LeadIndexQuery $query): StreamedResponse
    {
        $rows = $query->getExportRows($workspace, $request->all());
        $fileName = sprintf('leads-%s-%s.csv', $workspace->slug, now()->format('YmdHis'));

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            $firstRow = $rows->first();

            if (is_array($firstRow)) {
                fputcsv($output, array_keys($firstRow));
            }

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(Workspace $workspace, Lead $lead, LeadIndexQuery $query): Response
    {
        abort_unless($lead->workspace_id === $workspace->getKey(), 404);

        $lead->load([
            'pipeline:id,name',
            'stage:id,pipeline_id,name,color,is_won,is_lost',
            'assignee:id,name',
            'convertedClient:id,company_name',
        ]);

        $activities = ActivityFeed::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('subject_type', Lead::class)
            ->where('subject_id', $lead->getKey())
            ->with(['user:id,name', 'comments.user:id,name'])
            ->latest('created_at')
            ->get();

        return $this->appShell(
            workspace: $workspace,
            screen: 'CRM/Leads/Show',
            title: 'Leads',
            payload: [
                'lead' => $query->transformLead($lead) + [
                    'converted_at' => $lead->converted_at?->toIso8601String(),
                    'converted_at_label' => $lead->converted_at?->diffForHumans(),
                    'converted_client' => $lead->convertedClient ? [
                        'id' => $lead->convertedClient->getKey(),
                        'company_name' => $lead->convertedClient->company_name,
                    ] : null,
                ],
                'activities' => LeadActivityResource::collection($activities)->resolve(),
            ],
        );
    }
}
