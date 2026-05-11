<?php

namespace App\Services\CRM;

use App\Models\AutomationWorkflow;
use App\Models\Workspace;

class LeadAutomationService
{
    public function update(Workspace $workspace, array $data): AutomationWorkflow
    {
        $enabledUserIds = collect($data['enabled_user_ids'] ?? [])
            ->filter()
            ->map(fn ($id): string => (string) $id)
            ->values()
            ->all();

        if ($enabledUserIds !== []) {
            $validIds = $workspace->users()
                ->whereIn('users.id', $enabledUserIds)
                ->pluck('users.id')
                ->map(fn ($id): string => (string) $id)
                ->all();

            $enabledUserIds = $validIds;
        }

        $workflow = AutomationWorkflow::query()->firstOrNew([
            'workspace_id' => $workspace->getKey(),
            'trigger_event' => 'lead_created',
        ]);

        $webhookUrl = $data['webhook_url']
            ?? $workflow->n8n_webhook_url
            ?? $workspace->n8n_webhook_url;

        $workflow->fill([
            'name' => $data['workflow_name'] ?: ($workflow->name ?: 'Lead WhatsApp Follow Up'),
            'n8n_webhook_url' => $webhookUrl,
            'is_active' => (bool) $data['enabled'],
            'config' => array_merge($workflow->config ?? [], [
                'channel' => 'whatsapp',
                'managed_from' => 'crm_leads',
                'enabled_user_ids' => $enabledUserIds,
            ]),
        ]);

        $workflow->workspace_id = $workspace->getKey();
        $workflow->trigger_event = 'lead_created';
        $workflow->save();

        if (filled($data['webhook_url']) && $workspace->n8n_webhook_url !== $data['webhook_url']) {
            $workspace->forceFill([
                'n8n_webhook_url' => $data['webhook_url'],
            ])->saveQuietly();
        }

        return $workflow;
    }
}
