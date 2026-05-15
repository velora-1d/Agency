<?php

namespace App\Http\Controllers\App\Marketing;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class SocialPostController extends Controller
{
    use BuildsAppShellResponse;

    public function show(Workspace $workspace, SocialPost $post): Response
    {
        abort_unless($post->workspace_id === $workspace->id, 404);

        $analytics = $post->analytics ?? [];

        return $this->appShell(
            workspace: $workspace,
            screen: 'Marketing/SocialPosts/Show',
            title: 'Detail Post: ' . ($post->title ?: 'Untitled'),
            payload: [
                'post' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'caption' => $post->caption,
                    'hashtags' => $post->hashtags,
                    'platforms' => $post->platforms ?? [],
                    'status' => $post->status,
                    'scheduled_at' => optional($post->scheduled_at)?->format('Y-m-d\TH:i'),
                    'posted_at' => optional($post->posted_at)?->format('Y-m-d\TH:i'),
                    'reach' => (int) data_get($analytics, 'reach', 0),
                    'engagement' => (int) data_get($analytics, 'engagement', 0),
                    'clicks' => (int) data_get($analytics, 'clicks', 0),
                    'updated_at_label' => optional($post->updated_at)?->diffForHumans(),
                ]
            ],
            activeLabel: 'Marketing',
        );
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'hashtags' => 'nullable|string',
            'platforms' => 'nullable|array',
            'status' => 'required|string|max:20',
            'scheduled_at' => 'nullable|date',
            'posted_at' => 'nullable|date',
        ]);

        $workspace->socialPosts()->create(array_merge($validated, [
            'created_by' => $request->user()->id,
        ]));

        return back()->with('success', 'Post sosial berhasil dibuat.');
    }

    public function update(Request $request, Workspace $workspace, SocialPost $post): RedirectResponse
    {
        abort_unless($post->workspace_id === $workspace->id, 404);

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'hashtags' => 'nullable|string',
            'platforms' => 'nullable|array',
            'status' => 'required|string|max:20',
            'scheduled_at' => 'nullable|date',
            'posted_at' => 'nullable|date',
        ]);

        $post->update($validated);

        return back()->with('success', 'Post sosial berhasil diperbarui.');
    }

    public function destroy(Workspace $workspace, SocialPost $post): RedirectResponse
    {
        abort_unless($post->workspace_id === $workspace->id, 404);
        $post->delete();

        return back()->with('success', 'Post sosial berhasil dihapus.');
    }
}
