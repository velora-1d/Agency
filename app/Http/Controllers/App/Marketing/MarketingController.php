<?php

namespace App\Http\Controllers\App\Marketing;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\MarketingCampaign;
use App\Models\Newsletter;
use App\Models\NewsletterSubscriber;
use App\Models\SocialPost;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Response;

class MarketingController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace): Response
    {
        $activeTab = $request->string('tab')->toString() ?: 'overview';

        $campaigns = MarketingCampaign::query()
            ->where('workspace_id', $workspace->id)
            ->latest()
            ->get()
            ->map(function (MarketingCampaign $campaign): array {
                $meta = $campaign->external_ids ?? [];

                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'type' => $campaign->type,
                    'status' => $campaign->status,
                    'budget' => (float) ($campaign->budget ?? 0),
                    'spend' => (float) ($campaign->spend ?? 0),
                    'start_date' => optional($campaign->start_date)?->format('Y-m-d'),
                    'end_date' => optional($campaign->end_date)?->format('Y-m-d'),
                    'start_date_label' => optional($campaign->start_date)?->format('d M Y') ?? 'Not set',
                    'end_date_label' => optional($campaign->end_date)?->format('d M Y') ?? 'Not set',
                    'roi_percentage' => (float) data_get($meta, 'roi_percentage', 0),
                    'leads_generated' => (int) data_get($meta, 'leads_generated', 0),
                    'traffic_sessions' => (int) data_get($meta, 'traffic_sessions', 0),
                    'primary_source' => data_get($meta, 'primary_source', 'unassigned'),
                    'external_reference' => data_get($meta, 'external_reference', ''),
                    'updated_at_label' => optional($campaign->updated_at)?->diffForHumans(),
                ];
            });

        $socialPosts = SocialPost::query()
            ->where('workspace_id', $workspace->id)
            ->with(['client:id,company_name', 'creator:id,name'])
            ->latest('scheduled_at')
            ->latest()
            ->get()
            ->map(function (SocialPost $post): array {
                $analytics = $post->analytics ?? [];

                return [
                    'id' => $post->id,
                    'client_id' => $post->client_id,
                    'title' => $post->title,
                    'caption' => $post->caption,
                    'hashtags' => $post->hashtags,
                    'platforms' => $post->platforms ?? [],
                    'status' => $post->status,
                    'scheduled_at' => optional($post->scheduled_at)?->format('Y-m-d\TH:i'),
                    'scheduled_at_label' => optional($post->scheduled_at)?->format('d M Y H:i') ?? 'Not scheduled',
                    'posted_at' => optional($post->posted_at)?->format('Y-m-d\TH:i'),
                    'posted_at_label' => optional($post->posted_at)?->format('d M Y H:i') ?? 'Not posted',
                    'reach' => (int) data_get($analytics, 'reach', 0),
                    'engagement' => (int) data_get($analytics, 'engagement', 0),
                    'clicks' => (int) data_get($analytics, 'clicks', 0),
                    'client' => $post->client ? [
                        'id' => $post->client->id,
                        'name' => $post->client->company_name,
                    ] : null,
                    'creator' => $post->creator ? [
                        'id' => $post->creator->id,
                        'name' => $post->creator->name,
                    ] : null,
                    'updated_at_label' => optional($post->updated_at)?->diffForHumans(),
                ];
            });

        $newsletters = Newsletter::query()
            ->where('workspace_id', $workspace->id)
            ->with('creator:id,name')
            ->latest()
            ->get()
            ->map(function (Newsletter $newsletter): array {
                $metrics = $newsletter->metrics ?? [];

                return [
                    'id' => $newsletter->id,
                    'subject' => $newsletter->subject,
                    'content' => $newsletter->content,
                    'excerpt' => Str::limit(trim(strip_tags($newsletter->content)), 180),
                    'status' => $newsletter->status,
                    'scheduled_at' => optional($newsletter->scheduled_at)?->format('Y-m-d\TH:i'),
                    'scheduled_at_label' => optional($newsletter->scheduled_at)?->format('d M Y H:i') ?? 'Not scheduled',
                    'sent_at' => optional($newsletter->sent_at)?->format('Y-m-d\TH:i'),
                    'sent_at_label' => optional($newsletter->sent_at)?->format('d M Y H:i') ?? 'Not sent',
                    'open_rate' => (float) data_get($metrics, 'open_rate', 0),
                    'click_rate' => (float) data_get($metrics, 'click_rate', 0),
                    'bounce_rate' => (float) data_get($metrics, 'bounce_rate', 0),
                    'unsubscribe_rate' => (float) data_get($metrics, 'unsubscribe_rate', 0),
                    'creator' => $newsletter->creator ? [
                        'id' => $newsletter->creator->id,
                        'name' => $newsletter->creator->name,
                    ] : null,
                    'updated_at_label' => optional($newsletter->updated_at)?->diffForHumans(),
                ];
            });

        $subscribers = NewsletterSubscriber::query()
            ->where('workspace_id', $workspace->id)
            ->with('client:id,company_name')
            ->latest()
            ->get()
            ->map(fn (NewsletterSubscriber $subscriber): array => [
                'id' => $subscriber->id,
                'client_id' => $subscriber->client_id,
                'name' => $subscriber->name,
                'email' => $subscriber->email,
                'is_active' => $subscriber->is_active,
                'unsubscribed_at' => optional($subscriber->unsubscribed_at)?->format('Y-m-d\TH:i'),
                'unsubscribed_at_label' => optional($subscriber->unsubscribed_at)?->format('d M Y H:i') ?? 'Still active',
                'client' => $subscriber->client ? [
                    'id' => $subscriber->client->id,
                    'name' => $subscriber->client->company_name,
                ] : null,
                'updated_at_label' => optional($subscriber->updated_at)?->diffForHumans(),
            ]);

        $clients = Client::query()
            ->where('workspace_id', $workspace->id)
            ->orderBy('company_name')
            ->get(['id', 'company_name'])
            ->map(fn (Client $client): array => [
                'id' => $client->id,
                'name' => $client->company_name,
            ]);

        $avgRoi = $campaigns->avg('roi_percentage') ?? 0;
        $avgOpenRate = $newsletters->avg('open_rate') ?? 0;
        $avgClickRate = $newsletters->avg('click_rate') ?? 0;
        $topPost = $socialPosts->sortByDesc('engagement')->first();

        $sourceBreakdown = $campaigns
            ->groupBy('primary_source')
            ->map(fn ($items, $source): array => [
                'source' => $source,
                'campaigns' => $items->count(),
                'leads' => $items->sum('leads_generated'),
                'sessions' => $items->sum('traffic_sessions'),
            ])
            ->sortByDesc('leads')
            ->values();

        return $this->appShell(
            workspace: $workspace,
            screen: 'Marketing/Index',
            title: 'Marketing',
            activeLabel: 'Marketing',
            payload: [
                'activeTab' => $activeTab,
                'campaigns' => $campaigns,
                'socialPosts' => $socialPosts,
                'newsletters' => $newsletters,
                'subscribers' => $subscribers,
                'options' => [
                    'clients' => $clients,
                    'campaignTypes' => ['google_ads', 'meta_ads', 'tiktok_ads', 'email', 'whatsapp', 'seo', 'content'],
                    'campaignStatuses' => ['planning', 'active', 'paused', 'completed'],
                    'campaignSources' => ['instagram', 'facebook', 'google', 'website', 'referral', 'tiktok', 'email'],
                    'postStatuses' => ['idea', 'draft', 'review', 'scheduled', 'posted'],
                    'platforms' => ['instagram', 'facebook', 'tiktok', 'linkedin', 'x'],
                    'newsletterStatuses' => ['draft', 'scheduled', 'sending', 'sent'],
                ],
                'summary' => [
                    'overview' => [
                        'total_campaigns' => $campaigns->count(),
                        'active_campaigns' => $campaigns->where('status', 'active')->count(),
                        'total_budget' => (float) $campaigns->sum('budget'),
                        'total_spend' => (float) $campaigns->sum('spend'),
                        'total_leads' => $campaigns->sum('leads_generated'),
                        'avg_roi' => round($avgRoi, 1),
                        'top_content' => $topPost['title'] ?? 'No post yet',
                    ],
                    'social' => [
                        'total_posts' => $socialPosts->count(),
                        'scheduled_posts' => $socialPosts->where('status', 'scheduled')->count(),
                        'posted_posts' => $socialPosts->where('status', 'posted')->count(),
                        'reach' => $socialPosts->sum('reach'),
                        'engagement' => $socialPosts->sum('engagement'),
                        'clicks' => $socialPosts->sum('clicks'),
                    ],
                    'email' => [
                        'total_newsletters' => $newsletters->count(),
                        'sent_newsletters' => $newsletters->where('status', 'sent')->count(),
                        'active_subscribers' => $subscribers->where('is_active', true)->count(),
                        'inactive_subscribers' => $subscribers->where('is_active', false)->count(),
                        'avg_open_rate' => round($avgOpenRate, 1),
                        'avg_click_rate' => round($avgClickRate, 1),
                    ],
                    'analytics' => [
                        'traffic_sessions' => $campaigns->sum('traffic_sessions'),
                        'meta_spend' => (float) $campaigns->where('type', 'meta_ads')->sum('spend'),
                        'tiktok_spend' => (float) $campaigns->where('type', 'tiktok_ads')->sum('spend'),
                        'email_open_rate' => round($avgOpenRate, 1),
                        'email_click_rate' => round($avgClickRate, 1),
                        'source_breakdown' => $sourceBreakdown,
                    ],
                ],
            ],
        );
    }

    public function storeCampaign(Request $request, Workspace $workspace): RedirectResponse
    {
        MarketingCampaign::create($this->validateCampaign($request, $workspace));

        return back()->with('success', 'Marketing campaign created successfully.');
    }

    public function updateCampaign(Request $request, Workspace $workspace, MarketingCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->workspace_id === $workspace->id, 404);
        $campaign->update($this->validateCampaign($request, $workspace, $campaign));

        return back()->with('success', 'Marketing campaign updated successfully.');
    }

    public function destroyCampaign(Workspace $workspace, MarketingCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->workspace_id === $workspace->id, 404);
        $campaign->delete();

        return back()->with('success', 'Marketing campaign deleted successfully.');
    }

    public function storePost(Request $request, Workspace $workspace): RedirectResponse
    {
        SocialPost::create($this->validatePost($request, $workspace));

        return back()->with('success', 'Social post created successfully.');
    }

    public function updatePost(Request $request, Workspace $workspace, SocialPost $socialPost): RedirectResponse
    {
        abort_unless($socialPost->workspace_id === $workspace->id, 404);
        $socialPost->update($this->validatePost($request, $workspace, $socialPost));

        return back()->with('success', 'Social post updated successfully.');
    }

    public function destroyPost(Workspace $workspace, SocialPost $socialPost): RedirectResponse
    {
        abort_unless($socialPost->workspace_id === $workspace->id, 404);
        $socialPost->delete();

        return back()->with('success', 'Social post deleted successfully.');
    }

    public function storeNewsletter(Request $request, Workspace $workspace): RedirectResponse
    {
        Newsletter::create($this->validateNewsletter($request, $workspace));

        return back()->with('success', 'Newsletter created successfully.');
    }

    public function updateNewsletter(Request $request, Workspace $workspace, Newsletter $newsletter): RedirectResponse
    {
        abort_unless($newsletter->workspace_id === $workspace->id, 404);
        $newsletter->update($this->validateNewsletter($request, $workspace, $newsletter));

        return back()->with('success', 'Newsletter updated successfully.');
    }

    public function destroyNewsletter(Workspace $workspace, Newsletter $newsletter): RedirectResponse
    {
        abort_unless($newsletter->workspace_id === $workspace->id, 404);
        $newsletter->delete();

        return back()->with('success', 'Newsletter deleted successfully.');
    }

    public function storeSubscriber(Request $request, Workspace $workspace): RedirectResponse
    {
        NewsletterSubscriber::create($this->validateSubscriber($request, $workspace));

        return back()->with('success', 'Newsletter subscriber created successfully.');
    }

    public function updateSubscriber(Request $request, Workspace $workspace, NewsletterSubscriber $subscriber): RedirectResponse
    {
        abort_unless($subscriber->workspace_id === $workspace->id, 404);
        $subscriber->update($this->validateSubscriber($request, $workspace, $subscriber));

        return back()->with('success', 'Newsletter subscriber updated successfully.');
    }

    public function destroySubscriber(Workspace $workspace, NewsletterSubscriber $subscriber): RedirectResponse
    {
        abort_unless($subscriber->workspace_id === $workspace->id, 404);
        $subscriber->delete();

        return back()->with('success', 'Newsletter subscriber deleted successfully.');
    }

    protected function validateCampaign(Request $request, Workspace $workspace, ?MarketingCampaign $campaign = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['google_ads', 'meta_ads', 'tiktok_ads', 'email', 'whatsapp', 'seo', 'content'])],
            'status' => ['required', Rule::in(['planning', 'active', 'paused', 'completed'])],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'spend' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'roi_percentage' => ['nullable', 'numeric'],
            'leads_generated' => ['nullable', 'integer', 'min:0'],
            'traffic_sessions' => ['nullable', 'integer', 'min:0'],
            'primary_source' => ['nullable', 'string', 'max:50'],
            'external_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['external_ids'] = array_merge($campaign?->external_ids ?? [], [
            'roi_percentage' => (float) ($validated['roi_percentage'] ?? 0),
            'leads_generated' => (int) ($validated['leads_generated'] ?? 0),
            'traffic_sessions' => (int) ($validated['traffic_sessions'] ?? 0),
            'primary_source' => $validated['primary_source'] ?? 'unassigned',
            'external_reference' => $validated['external_reference'] ?? '',
        ]);

        unset(
            $validated['roi_percentage'],
            $validated['leads_generated'],
            $validated['traffic_sessions'],
            $validated['primary_source'],
            $validated['external_reference'],
        );

        return $validated;
    }

    protected function validatePost(Request $request, Workspace $workspace, ?SocialPost $post = null): array
    {
        $validated = $request->validate([
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
            ],
            'title' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'hashtags' => ['nullable', 'string'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['string', Rule::in(['instagram', 'facebook', 'tiktok', 'linkedin', 'x'])],
            'status' => ['required', Rule::in(['idea', 'draft', 'review', 'scheduled', 'posted'])],
            'scheduled_at' => ['nullable', 'date'],
            'posted_at' => ['nullable', 'date'],
            'reach' => ['nullable', 'integer', 'min:0'],
            'engagement' => ['nullable', 'integer', 'min:0'],
            'clicks' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = $post?->created_by ?? $request->user()?->id;
        $validated['analytics'] = [
            'reach' => (int) ($validated['reach'] ?? 0),
            'engagement' => (int) ($validated['engagement'] ?? 0),
            'clicks' => (int) ($validated['clicks'] ?? 0),
        ];

        unset($validated['reach'], $validated['engagement'], $validated['clicks']);

        return $validated;
    }

    protected function validateNewsletter(Request $request, Workspace $workspace, ?Newsletter $newsletter = null): array
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'sending', 'sent'])],
            'scheduled_at' => ['nullable', 'date'],
            'sent_at' => ['nullable', 'date'],
            'open_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'click_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bounce_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'unsubscribe_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = $newsletter?->created_by ?? $request->user()?->id;
        $validated['metrics'] = [
            'open_rate' => (float) ($validated['open_rate'] ?? 0),
            'click_rate' => (float) ($validated['click_rate'] ?? 0),
            'bounce_rate' => (float) ($validated['bounce_rate'] ?? 0),
            'unsubscribe_rate' => (float) ($validated['unsubscribe_rate'] ?? 0),
        ];

        unset(
            $validated['open_rate'],
            $validated['click_rate'],
            $validated['bounce_rate'],
            $validated['unsubscribe_rate'],
        );

        return $validated;
    }

    protected function validateSubscriber(Request $request, Workspace $workspace, ?NewsletterSubscriber $subscriber = null): array
    {
        $validated = $request->validate([
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
            ],
            'name' => ['nullable', 'string', 'max:100'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('newsletter_subscribers', 'email')
                    ->ignore($subscriber?->id)
                    ->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
            ],
            'is_active' => ['nullable', 'boolean'],
            'unsubscribed_at' => ['nullable', 'date'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['is_active']) {
            $validated['unsubscribed_at'] = null;
        } elseif (blank($validated['unsubscribed_at'] ?? null)) {
            $validated['unsubscribed_at'] = now();
        }

        return $validated;
    }
}
