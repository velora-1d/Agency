<?php

namespace App\Modules\Automation\AiTools\Queries;

use App\Models\Workspace;

class AiToolIndexQuery
{
    public function getIndexPayload(Workspace $workspace): array
    {
        $items = [
            [
                'id' => 'writing-assistant',
                'name' => 'AI Writing',
                'type' => 'writing',
                'surface' => 'Proposal, caption, SOP, email',
                'model' => 'Laravel AI SDK',
                'status' => 'active',
                'usage_label' => '142 prompts / month',
                'last_run_label' => '2 minutes ago',
                'description' => 'Bikin draft cepat untuk tim sales, ops, dan marketing dengan tone workspace yang konsisten.',
                'scopes' => ['Marketing', 'Sales', 'Internal docs'],
                'guardrails' => ['Brand voice', 'No secrets', 'Human review'],
                'metrics' => ['92% accepted', '1.4s avg', '3 templates'],
            ],
            [
                'id' => 'summary-engine',
                'name' => 'AI Summary',
                'type' => 'summary',
                'surface' => 'Meeting notes, document, call transcript',
                'model' => 'Laravel AI SDK',
                'status' => 'active',
                'usage_label' => '88 summaries / month',
                'last_run_label' => '15 minutes ago',
                'description' => 'Ringkas notulen dan dokumen panjang menjadi poin kerja yang langsung bisa dipakai.',
                'scopes' => ['Meetings', 'Notes', 'Files'],
                'guardrails' => ['Keep context', 'Mention action items', 'Cite source'],
                'metrics' => ['87% useful', '28s average', '5 presets'],
            ],
            [
                'id' => 'reply-coach',
                'name' => 'AI Reply Suggestion',
                'type' => 'reply',
                'surface' => 'Inbox & support ticket',
                'model' => 'Claude / OpenAI',
                'status' => 'active',
                'usage_label' => '64 suggestions / month',
                'last_run_label' => '8 minutes ago',
                'description' => 'Saran balasan yang lebih cepat untuk chat client dan ticket support tanpa kehilangan konteks.',
                'scopes' => ['Inbox', 'Support', 'Customer success'],
                'guardrails' => ['Tone friendly', 'No legal promise', 'Escalate risky cases'],
                'metrics' => ['73% reused', '2.2s avg', '4 tone variants'],
            ],
            [
                'id' => 'lead-scoring',
                'name' => 'AI Lead Scoring',
                'type' => 'scoring',
                'surface' => 'CRM leads and pipeline',
                'model' => 'Laravel AI SDK',
                'status' => 'draft',
                'usage_label' => '19 evaluations / month',
                'last_run_label' => 'Yesterday',
                'description' => 'Skor lead dari source, engagement, dan nilai potensi agar pipeline lebih tajam.',
                'scopes' => ['Leads', 'Pipeline', 'CRM automation'],
                'guardrails' => ['Explain score', 'Human override', 'Avoid bias'],
                'metrics' => ['78% aligned', '11 rules', '1 explainability layer'],
            ],
            [
                'id' => 'chat-assistant',
                'name' => 'AI Chat Assistant',
                'type' => 'assistant',
                'surface' => 'Project & client question answering',
                'model' => 'Claude / OpenAI',
                'status' => 'active',
                'usage_label' => '51 chats / month',
                'last_run_label' => 'Now',
                'description' => 'Tanya status project, client, file, dan progres kerja dengan jawaban yang lebih terarah.',
                'scopes' => ['Projects', 'Clients', 'Files', 'Notes'],
                'guardrails' => ['Workspace only', 'Cite relevant data', 'No external claims'],
                'metrics' => ['81% confidence', '9 sources', '2 response modes'],
            ],
        ];

        return [
            'ai_tools' => [
                'workspace_name' => $workspace->name,
                'summary' => [
                    'total_tools' => count($items),
                    'active_tools' => count(array_filter($items, fn ($item) => $item['status'] === 'active')),
                    'draft_tools' => count(array_filter($items, fn ($item) => $item['status'] === 'draft')),
                    'prompts_this_month' => 364,
                    'assistant_surfaces' => 4,
                    'guardrails_count' => 12,
                ],
                'filters' => [
                    'types' => [
                        ['value' => '', 'label' => 'All Types'],
                        ['value' => 'writing', 'label' => 'Writing'],
                        ['value' => 'summary', 'label' => 'Summary'],
                        ['value' => 'reply', 'label' => 'Reply'],
                        ['value' => 'scoring', 'label' => 'Scoring'],
                        ['value' => 'assistant', 'label' => 'Assistant'],
                    ],
                    'statuses' => [
                        ['value' => '', 'label' => 'All Status'],
                        ['value' => 'active', 'label' => 'Active'],
                        ['value' => 'draft', 'label' => 'Draft'],
                        ['value' => 'paused', 'label' => 'Paused'],
                    ],
                ],
                'items' => $items,
                'logs' => [
                    ['id' => 'ai-log-1', 'label' => 'Lead reply drafted', 'time_label' => '2 minutes ago', 'detail' => 'Reply suggestion generated for lead follow-up.'],
                    ['id' => 'ai-log-2', 'label' => 'Meeting summary queued', 'time_label' => '15 minutes ago', 'detail' => 'Summary model processed 6 meeting notes.'],
                    ['id' => 'ai-log-3', 'label' => 'Score recalculated', 'time_label' => 'Yesterday', 'detail' => 'Lead score rules refreshed for pipeline.'],
                ],
                'presets' => [
                    'caption', 'proposal', 'sop', 'meeting summary', 'reply suggestion',
                ],
            ],
        ];
    }
}
