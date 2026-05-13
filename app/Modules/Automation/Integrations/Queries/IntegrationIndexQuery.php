<?php

namespace App\Modules\Automation\Integrations\Queries;

use App\Models\Workspace;

class IntegrationIndexQuery
{
    public function getIndexPayload(Workspace $workspace): array
    {
        $items = [
            [
                'id' => 'wa-evolution',
                'name' => 'WhatsApp Evolution',
                'provider' => 'WhatsApp',
                'status' => 'connected',
                'health' => 'healthy',
                'health_score' => 98,
                'last_checked_label' => '3 minutes ago',
                'endpoint' => 'https://wa.example.com',
                'scope' => 'Inbox, automation, reply suggestion',
                'modules' => ['Inbox', 'Automation', 'Support'],
            ],
            [
                'id' => 'google-workspace',
                'name' => 'Google Workspace',
                'provider' => 'Google',
                'status' => 'connected',
                'health' => 'healthy',
                'health_score' => 95,
                'last_checked_label' => '11 minutes ago',
                'endpoint' => 'Gmail / Calendar / Drive',
                'scope' => 'Calendar sync, drive sync, email actions',
                'modules' => ['Calendar', 'Files', 'Inbox'],
            ],
            [
                'id' => 'meta-ads',
                'name' => 'Meta Ads',
                'provider' => 'Meta',
                'status' => 'connected',
                'health' => 'warning',
                'health_score' => 71,
                'last_checked_label' => '28 minutes ago',
                'endpoint' => 'Leads API',
                'scope' => 'Lead ingestion and campaign sync',
                'modules' => ['CRM', 'Automation', 'Marketing'],
            ],
            [
                'id' => 'openai-claude',
                'name' => 'OpenAI / Claude API',
                'provider' => 'AI',
                'status' => 'connected',
                'health' => 'healthy',
                'health_score' => 93,
                'last_checked_label' => 'Now',
                'endpoint' => 'Model gateway',
                'scope' => 'Writing, summary, scoring, assistant',
                'modules' => ['AI Tools', 'Automation'],
            ],
            [
                'id' => 'zapier-n8n',
                'name' => 'Zapier / n8n Webhook',
                'provider' => 'Webhook',
                'status' => 'pending',
                'health' => 'warning',
                'health_score' => 64,
                'last_checked_label' => '1 hour ago',
                'endpoint' => 'n8n webhook relay',
                'scope' => 'External workflow handoff',
                'modules' => ['Automation'],
            ],
            [
                'id' => 'cloudflare',
                'name' => 'Cloudflare',
                'provider' => 'Cloudflare',
                'status' => 'disconnected',
                'health' => 'offline',
                'health_score' => 28,
                'last_checked_label' => 'Yesterday',
                'endpoint' => 'DNS / R2 / proxy',
                'scope' => 'Domain, storage, and edge config',
                'modules' => ['Domains', 'Hosting', 'Files'],
            ],
        ];

        return [
            'integrations' => [
                'workspace_name' => $workspace->name,
                'summary' => [
                    'total_integrations' => count($items),
                    'connected_integrations' => count(array_filter($items, fn ($item) => $item['status'] === 'connected')),
                    'warning_integrations' => count(array_filter($items, fn ($item) => $item['health'] === 'warning')),
                    'disconnected_integrations' => count(array_filter($items, fn ($item) => $item['status'] === 'disconnected')),
                    'health_score' => 83,
                    'ping_checks' => 128,
                ],
                'filters' => [
                    'providers' => [
                        ['value' => '', 'label' => 'All Providers'],
                        ['value' => 'WhatsApp', 'label' => 'WhatsApp'],
                        ['value' => 'Google', 'label' => 'Google'],
                        ['value' => 'Meta', 'label' => 'Meta'],
                        ['value' => 'AI', 'label' => 'AI'],
                        ['value' => 'Webhook', 'label' => 'Webhook'],
                        ['value' => 'Cloudflare', 'label' => 'Cloudflare'],
                    ],
                    'statuses' => [
                        ['value' => '', 'label' => 'All Status'],
                        ['value' => 'connected', 'label' => 'Connected'],
                        ['value' => 'pending', 'label' => 'Pending'],
                        ['value' => 'disconnected', 'label' => 'Disconnected'],
                    ],
                ],
                'items' => $items,
                'checks' => [
                    ['id' => 'check-1', 'label' => 'WhatsApp handshake', 'time_label' => '3 minutes ago', 'result' => 'healthy'],
                    ['id' => 'check-2', 'label' => 'Google token refresh', 'time_label' => '11 minutes ago', 'result' => 'healthy'],
                    ['id' => 'check-3', 'label' => 'Cloudflare DNS ping', 'time_label' => 'Yesterday', 'result' => 'offline'],
                ],
            ],
        ];
    }
}
