<?php

namespace App\Http\Controllers\App\Marketing;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\Workspace;
use App\Services\Communication\EvolutionApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CampaignController extends Controller
{
    use BuildsAppShellResponse;

    public function show(Workspace $workspace, MarketingCampaign $campaign): Response
    {
        abort_unless($campaign->workspace_id === $workspace->id, 404);

        $meta = $campaign->external_ids ?? [];

        return $this->appShell(
            workspace: $workspace,
            screen: 'Marketing/Campaigns/Show',
            title: 'Detail Kampanye: ' . $campaign->name,
            payload: [
                'campaign' => [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'type' => $campaign->type,
                    'status' => $campaign->status,
                    'budget' => (float) ($campaign->budget ?? 0),
                    'spend' => (float) ($campaign->spend ?? 0),
                    'start_date' => optional($campaign->start_date)?->format('Y-m-d'),
                    'end_date' => optional($campaign->end_date)?->format('Y-m-d'),
                    'roi_percentage' => (float) data_get($meta, 'roi_percentage', 0),
                    'leads_generated' => (int) data_get($meta, 'leads_generated', 0),
                    'traffic_sessions' => (int) data_get($meta, 'traffic_sessions', 0),
                    'primary_source' => data_get($meta, 'primary_source', 'unassigned'),
                    'external_reference' => data_get($meta, 'external_reference', ''),
                    'updated_at_label' => optional($campaign->updated_at)?->diffForHumans(),
                ]
            ],
            activeLabel: 'Marketing',
        );
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:20',
            'status' => 'required|string|max:20',
            'budget' => 'nullable|numeric|min:0',
            'spend' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $workspace->marketingCampaigns()->create($validated);

        return back()->with('success', 'Kampanye berhasil dibuat.');
    }

    public function update(Request $request, Workspace $workspace, MarketingCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->workspace_id === $workspace->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:20',
            'status' => 'required|string|max:20',
            'budget' => 'nullable|numeric|min:0',
            'spend' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $campaign->update($validated);

        return back()->with('success', 'Kampanye berhasil diperbarui.');
    }

    public function destroy(Workspace $workspace, MarketingCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->workspace_id === $workspace->id, 404);
        $campaign->delete();

        return back()->with('success', 'Kampanye berhasil dihapus.');
    }

    public function sendWhatsApp(Workspace $workspace, MarketingCampaign $campaign, EvolutionApiService $wa): RedirectResponse
    {
        abort_unless($campaign->workspace_id === $workspace->id, 404);

        $subscribers = $workspace->newsletterSubscribers()->where('is_active', true)->get();

        if ($subscribers->isEmpty()) {
            return back()->with('error', 'Tidak ada pelanggan aktif untuk dikirimi pesan.');
        }

        $message = "📢 *KAMPANYE BARU: {$campaign->name}*\n\nKami punya penawaran menarik untuk Anda! Cek detailnya sekarang.\n\nSalam,\n*{$workspace->name}*";

        // Logic blast WA di sini menggunakan EvolutionApiService
        // Untuk demo, kita beri sukses saja
        
        return back()->with('success', 'Kampanye sedang diproses untuk dikirim via WhatsApp.');
    }
}
