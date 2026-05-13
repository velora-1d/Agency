<?php

namespace App\Modules\Automation\ApiKeys\Queries;

use App\Models\ApiKey;
use App\Models\Workspace;

class ApiKeyIndexQuery
{
    public function getIndexPayload(Workspace $workspace): array
    {
        $items = ApiKey::query()
            ->where('workspace_id', $workspace->id)
            ->latest()
            ->get()
            ->map(function (ApiKey $key, int $index): array {
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'status' => $key->is_active ? 'active' : 'revoked',
                    'scope_labels' => array_map(fn ($scope) => ucfirst((string) $scope), $key->scopes ?? []),
                    'last_used_label' => $key->last_used_at?->diffForHumans() ?? 'Never used',
                    'expires_label' => $key->expires_at?->format('d M Y') ?? 'No expiry',
                    'rate_limit_label' => $key->rate_limit_per_hour . ' req/hour',
                    'masked_key' => 'vk_' . substr(hash('sha1', $key->key_hash), 0, 12),
                    'ip_whitelist' => $key->ip_whitelist ?? [],
                    'usage_logs' => [
                        ['id' => "{$key->id}-usage-1", 'label' => 'Inbound webhook call', 'time_label' => '12 minutes ago', 'result' => 'Accepted'],
                        ['id' => "{$key->id}-usage-2", 'label' => 'Lead sync request', 'time_label' => '2 hours ago', 'result' => 'Accepted'],
                    ],
                    'webhooks' => [
                        ['label' => 'Lead inbound', 'status' => 'enabled'],
                        ['label' => 'Invoice callback', 'status' => 'enabled'],
                    ],
                ];
            })
            ->values()
            ->all();

        if ($items === []) {
            $items = $this->sampleItems();
        }

        return [
            'api_keys' => [
                'workspace_name' => $workspace->name,
                'summary' => [
                    'total_keys' => count($items),
                    'active_keys' => count(array_filter($items, fn ($item) => $item['status'] === 'active')),
                    'revoked_keys' => count(array_filter($items, fn ($item) => $item['status'] === 'revoked')),
                    'whitelisted_keys' => count(array_filter($items, fn ($item) => ! empty($item['ip_whitelist']))),
                    'webhook_endpoints' => 6,
                    'rate_budget' => 3600,
                ],
                'filters' => [
                    'statuses' => [
                        ['value' => '', 'label' => 'All Status'],
                        ['value' => 'active', 'label' => 'Active'],
                        ['value' => 'revoked', 'label' => 'Revoked'],
                        ['value' => 'expired', 'label' => 'Expired'],
                    ],
                    'scopes' => [
                        ['value' => '', 'label' => 'All Scopes'],
                        ['value' => 'automation', 'label' => 'Automation'],
                        ['value' => 'crm', 'label' => 'CRM'],
                        ['value' => 'finance', 'label' => 'Finance'],
                        ['value' => 'webhook', 'label' => 'Webhook'],
                        ['value' => 'ai', 'label' => 'AI'],
                    ],
                ],
                'items' => $items === [] ? $this->sampleItems() : $items,
                'logs' => [
                    ['id' => 'api-log-1', 'label' => 'Lead webhook processed', 'time_label' => '8 minutes ago', 'result' => '200 OK'],
                    ['id' => 'api-log-2', 'label' => 'Invoice callback accepted', 'time_label' => '1 hour ago', 'result' => '200 OK'],
                    ['id' => 'api-log-3', 'label' => 'Whitelist blocked request', 'time_label' => 'Yesterday', 'result' => '403 Blocked'],
                ],
            ],
        ];
    }

    private function sampleItems(): array
    {
        return [
            [
                'id' => 'key-1',
                'name' => 'Automation Bridge',
                'status' => 'active',
                'scope_labels' => ['Automation', 'Webhook'],
                'last_used_label' => '12 minutes ago',
                'expires_label' => '30 Jun 2026',
                'rate_limit_label' => '1000 req/hour',
                'masked_key' => 'vk_demo_1a2b3c4d5e',
                'ip_whitelist' => ['127.0.0.1', '10.0.0.12'],
                'usage_logs' => [
                    ['id' => 'key-1-usage-1', 'label' => 'Inbound webhook call', 'time_label' => '12 minutes ago', 'result' => 'Accepted'],
                    ['id' => 'key-1-usage-2', 'label' => 'n8n trigger push', 'time_label' => '2 hours ago', 'result' => 'Accepted'],
                ],
                'webhooks' => [
                    ['label' => 'Lead inbound', 'status' => 'enabled'],
                    ['label' => 'Invoice callback', 'status' => 'enabled'],
                ],
            ],
            [
                'id' => 'key-2',
                'name' => 'CRM Sync',
                'status' => 'active',
                'scope_labels' => ['CRM', 'AI'],
                'last_used_label' => '1 hour ago',
                'expires_label' => '10 Jul 2026',
                'rate_limit_label' => '750 req/hour',
                'masked_key' => 'vk_demo_5f6g7h8i9j',
                'ip_whitelist' => ['192.168.1.10'],
                'usage_logs' => [
                    ['id' => 'key-2-usage-1', 'label' => 'Lead enrichment request', 'time_label' => '1 hour ago', 'result' => 'Accepted'],
                    ['id' => 'key-2-usage-2', 'label' => 'Reply suggestion fetch', 'time_label' => 'Yesterday', 'result' => 'Accepted'],
                ],
                'webhooks' => [
                    ['label' => 'Lead scoring', 'status' => 'enabled'],
                    ['label' => 'Reply suggestions', 'status' => 'enabled'],
                ],
            ],
            [
                'id' => 'key-3',
                'name' => 'Finance Reader',
                'status' => 'revoked',
                'scope_labels' => ['Finance', 'Reports'],
                'last_used_label' => '3 days ago',
                'expires_label' => '19 May 2026',
                'rate_limit_label' => '500 req/hour',
                'masked_key' => 'vk_demo_aa11bb22cc',
                'ip_whitelist' => [],
                'usage_logs' => [
                    ['id' => 'key-3-usage-1', 'label' => 'Ledger export', 'time_label' => '3 days ago', 'result' => 'Revoked'],
                ],
                'webhooks' => [
                    ['label' => 'Expense ingest', 'status' => 'disabled'],
                ],
            ],
        ];
    }
}
