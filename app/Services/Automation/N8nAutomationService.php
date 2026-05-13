<?php

namespace App\Services\Automation;

use App\Models\AutomationWorkflow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nAutomationService
{
    public function __construct(protected AutomationWorkflowService $workflowService)
    {
    }

    public function trigger(string $workspaceId, string $event, array $data): void
    {
        $workflows = AutomationWorkflow::where('workspace_id', $workspaceId)
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->get();

        foreach ($workflows as $workflow) {
            $startedAt = now();

            if (! $workflow->n8n_webhook_url) {
                $this->workflowService->recordRun($workflow, [
                    'status' => 'skipped',
                    'trigger_event' => $event,
                    'message' => 'Webhook URL belum diisi, run dilewati.',
                    'payload' => [
                        'event' => $event,
                        'data' => $data,
                    ],
                    'started_at' => $startedAt,
                    'finished_at' => now(),
                ]);

                continue;
            }

            $enabledUserIds = collect($workflow->config['enabled_user_ids'] ?? [])
                ->filter()
                ->map(fn ($id): string => (string) $id)
                ->all();

            if ($enabledUserIds !== []) {
                $assignedTo = $data['assigned_to'] ?? null;

                if (! $assignedTo || ! in_array((string) $assignedTo, $enabledUserIds, true)) {
                    $this->workflowService->recordRun($workflow, [
                        'status' => 'skipped',
                        'trigger_event' => $event,
                        'message' => 'Workflow dibatasi ke assignee tertentu.',
                        'payload' => [
                            'event' => $event,
                            'data' => $data,
                        ],
                        'started_at' => $startedAt,
                        'finished_at' => now(),
                    ]);

                    continue;
                }
            }

            try {
                Http::post($workflow->n8n_webhook_url, [
                    'event' => $event,
                    'timestamp' => now()->toIso8601String(),
                    'data' => $data,
                ]);

                $this->workflowService->recordRun($workflow, [
                    'status' => 'success',
                    'trigger_event' => $event,
                    'message' => 'Workflow berhasil dikirim ke n8n.',
                    'payload' => [
                        'event' => $event,
                        'data' => $data,
                        'webhook_url' => $workflow->n8n_webhook_url,
                    ],
                    'started_at' => $startedAt,
                    'finished_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error("N8n Automation Trigger Failed for workflow {$workflow->id}: " . $e->getMessage());

                $this->workflowService->recordRun($workflow, [
                    'status' => 'failed',
                    'trigger_event' => $event,
                    'message' => $e->getMessage(),
                    'payload' => [
                        'event' => $event,
                        'data' => $data,
                        'webhook_url' => $workflow->n8n_webhook_url,
                    ],
                    'started_at' => $startedAt,
                    'finished_at' => now(),
                ]);
            }
        }
    }
}
