<?php

namespace App\Services\CRM;

use App\Models\ActivityFeed;
use App\Models\ActivityComment;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class LeadService
{
    public function __construct(protected LeadScoringService $scoringService)
    {
    }

    public function create(Workspace $workspace, array $data): Lead
    {
        [$pipelineId, $stageId] = $this->resolvePipelineStage($workspace, $data['pipeline_id'] ?? null, $data['stage_id'] ?? null);

        return Lead::query()->create([
            'workspace_id' => $workspace->getKey(),
            'pipeline_id' => $pipelineId,
            'stage_id' => $stageId,
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'province' => $data['province'] ?? null,
            'source' => $data['source'] ?? null,
            'estimated_value' => $data['estimated_value'] ?? null,
            'priority' => $data['priority'],
            'assigned_to' => $this->resolveAssignee($workspace, $data['assigned_to'] ?? null),
            'notes' => $data['notes'] ?? null,
            'ai_score' => $this->scoringService->score($data),
        ]);
    }

    public function update(Workspace $workspace, Lead $lead, array $data): Lead
    {
        abort_unless($lead->workspace_id === $workspace->getKey(), 404);

        [$pipelineId, $stageId] = $this->resolvePipelineStage($workspace, $data['pipeline_id'] ?? null, $data['stage_id'] ?? null);

        $lead->update([
            'pipeline_id' => $pipelineId,
            'stage_id' => $stageId,
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'province' => $data['province'] ?? null,
            'source' => $data['source'] ?? null,
            'estimated_value' => $data['estimated_value'] ?? null,
            'priority' => $data['priority'],
            'assigned_to' => $this->resolveAssignee($workspace, $data['assigned_to'] ?? null),
            'notes' => $data['notes'] ?? null,
            'ai_score' => $this->scoringService->score($data),
        ]);

        return $lead->refresh();
    }

    public function moveStage(Workspace $workspace, Lead $lead, ?string $pipelineId, ?string $stageId): Lead
    {
        abort_unless($lead->workspace_id === $workspace->getKey(), 404);

        [$resolvedPipelineId, $resolvedStageId] = $this->resolvePipelineStage($workspace, $pipelineId, $stageId);

        $lead->update([
            'pipeline_id' => $resolvedPipelineId,
            'stage_id' => $resolvedStageId,
        ]);

        return $lead->refresh();
    }

    public function updateNotes(Workspace $workspace, Lead $lead, ?string $notes): Lead
    {
        abort_unless($lead->workspace_id === $workspace->getKey(), 404);

        $lead->update([
            'notes' => $notes,
        ]);

        return $lead->refresh();
    }

    public function addActivity(Workspace $workspace, Lead $lead, string $content): ActivityFeed
    {
        abort_unless($lead->workspace_id === $workspace->getKey(), 404);

        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'lead',
            'subject_type' => Lead::class,
            'subject_id' => $lead->getKey(),
            'description' => $content,
            'metadata' => [
                'action' => 'note',
                'icon' => 'NotebookPen',
                'color' => 'amber',
            ],
        ]);
    }

    public function convertToClient(Workspace $workspace, Lead $lead): \App\Models\Client
    {
        abort_unless($lead->workspace_id === $workspace->getKey(), 404);
        abort_if($lead->converted_at !== null, 422, 'Lead ini sudah dikonversi menjadi client.');

        return \Illuminate\Support\Facades\DB::transaction(function () use ($workspace, $lead) {
            $client = \App\Models\Client::query()->create([
                'workspace_id' => $workspace->getKey(),
                'lead_id' => $lead->getKey(),
                'company_name' => $lead->company ?: $lead->name,
                'pic_name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'address' => $lead->address,
                'city' => $lead->city,
                'province' => $lead->province,
                'status' => 'active',
                'assigned_to' => $lead->assigned_to,
                'notes' => $lead->notes,
                'portal_access' => false,
            ]);

            $lead->update([
                'converted_at' => now(),
                'converted_to_client_id' => $client->getKey(),
            ]);

            // Optional: Move to WON stage if available in the same pipeline
            $wonStage = \App\Models\PipelineStage::query()
                ->where('pipeline_id', $lead->pipeline_id)
                ->where('is_won', true)
                ->first();

            if ($wonStage) {
                $lead->update(['stage_id' => $wonStage->id]);
            }

            $this->addActivity($workspace, $lead, sprintf('Lead dikonversi menjadi Client: %s', $client->company_name));

            return $client;
        });
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    protected function resolvePipelineStage(Workspace $workspace, ?string $pipelineId, ?string $stageId): array
    {
        $pipeline = null;
        $stage = null;

        if ($pipelineId) {
            $pipeline = Pipeline::query()
                ->where('workspace_id', $workspace->getKey())
                ->find($pipelineId);

            abort_if($pipeline === null, 422, 'Selected pipeline is invalid.');
        }

        if ($stageId) {
            $stage = PipelineStage::query()->find($stageId);
            abort_if($stage === null, 422, 'Selected stage is invalid.');

            if ($pipeline !== null) {
                abort_unless($stage->pipeline_id === $pipeline->getKey(), 422);
            } else {
                $pipeline = Pipeline::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->find($stage->pipeline_id);

                abort_if($pipeline === null, 422, 'Selected stage is invalid for this workspace.');
            }
        }

        return [$pipeline?->getKey(), $stage?->getKey()];
    }

    protected function resolveAssignee(Workspace $workspace, ?string $assigneeId): ?string
    {
        if (! $assigneeId) {
            return null;
        }

        $exists = $workspace->users()->where('users.id', $assigneeId)->exists();
        abort_unless($exists, 422);

        return $assigneeId;
    }
}
