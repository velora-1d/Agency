<?php

namespace App\Services\Automation;

use App\Models\AutomationWorkflow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nAutomationService
{
    public function trigger(string $workspaceId, string $event, array $data): void
    {
        $workflows = AutomationWorkflow::where('workspace_id', $workspaceId)
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->get();

        foreach ($workflows as $workflow) {
            if (!$workflow->n8n_webhook_url) continue;

            $enabledUserIds = collect($workflow->config['enabled_user_ids'] ?? [])
                ->filter()
                ->map(fn ($id): string => (string) $id)
                ->all();

            if ($enabledUserIds !== []) {
                $assignedTo = $data['assigned_to'] ?? null;

                if (! $assignedTo || ! in_array((string) $assignedTo, $enabledUserIds, true)) {
                    continue;
                }
            }

            try {
                Http::post($workflow->n8n_webhook_url, [
                    'event' => $event,
                    'timestamp' => now()->toIso8601String(),
                    'data' => $data,
                ]);
            } catch (\Exception $e) {
                Log::error("N8n Automation Trigger Failed for workflow {$workflow->id}: " . $e->getMessage());
            }
        }
    }
}
