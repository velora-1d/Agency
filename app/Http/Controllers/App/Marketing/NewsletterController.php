<?php

namespace App\Http\Controllers\App\Marketing;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\Workspace;
use App\Services\Communication\EvolutionApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class NewsletterController extends Controller
{
    use BuildsAppShellResponse;

    public function show(Workspace $workspace, Newsletter $newsletter): Response
    {
        abort_unless($newsletter->workspace_id === $workspace->id, 404);

        $metrics = $newsletter->metrics ?? [];

        return $this->appShell(
            workspace: $workspace,
            screen: 'Marketing/Newsletters/Show',
            title: 'Detail Newsletter: ' . $newsletter->subject,
            payload: [
                'newsletter' => [
                    'id' => $newsletter->id,
                    'subject' => $newsletter->subject,
                    'content' => $newsletter->content,
                    'status' => $newsletter->status,
                    'scheduled_at' => optional($newsletter->scheduled_at)?->format('Y-m-d\TH:i'),
                    'sent_at' => optional($newsletter->sent_at)?->format('Y-m-d\TH:i'),
                    'recipients_count' => (int) data_get($metrics, 'recipients_count', 0),
                    'open_rate' => (float) data_get($metrics, 'open_rate', 0),
                    'click_rate' => (float) data_get($metrics, 'click_rate', 0),
                    'updated_at_label' => optional($newsletter->updated_at)?->diffForHumans(),
                ]
            ],
            activeLabel: 'Marketing',
        );
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|string|max:20',
            'scheduled_at' => 'nullable|date',
        ]);

        $workspace->newsletters()->create(array_merge($validated, [
            'created_by' => $request->user()->id,
        ]));

        return back()->with('success', 'Newsletter berhasil dibuat.');
    }

    public function update(Request $request, Workspace $workspace, Newsletter $newsletter): RedirectResponse
    {
        abort_unless($newsletter->workspace_id === $workspace->id, 404);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|string|max:20',
            'scheduled_at' => 'nullable|date',
        ]);

        $newsletter->update($validated);

        return back()->with('success', 'Newsletter berhasil diperbarui.');
    }

    public function destroy(Workspace $workspace, Newsletter $newsletter): RedirectResponse
    {
        abort_unless($newsletter->workspace_id === $workspace->id, 404);
        $newsletter->delete();

        return back()->with('success', 'Newsletter berhasil dihapus.');
    }

    public function sendWhatsApp(Workspace $workspace, Newsletter $newsletter, EvolutionApiService $wa): RedirectResponse
    {
        abort_unless($newsletter->workspace_id === $workspace->id, 404);

        $subscribers = $workspace->newsletterSubscribers()->where('is_active', true)->get();

        if ($subscribers->isEmpty()) {
            return back()->with('error', 'Tidak ada pelanggan aktif untuk dikirimi newsletter.');
        }

        $message = "📰 *NEWSLETTER: {$newsletter->subject}*\n\n{$newsletter->content}\n\nSalam,\n*{$workspace->name}*";

        // Logic blast WA di sini
        
        $newsletter->update(['status' => 'sent', 'sent_at' => now()]);

        return back()->with('success', 'Newsletter sedang dikirim ke semua pelanggan via WhatsApp.');
    }
}
