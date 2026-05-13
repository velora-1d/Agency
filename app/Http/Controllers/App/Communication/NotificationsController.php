<?php

namespace App\Http\Controllers\App\Communication;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Response;

class NotificationsController extends Controller
{
    use BuildsAppShellResponse;

    public function __invoke(Request $request, Workspace $workspace): Response
    {
        $notifications = WorkspaceNotification::query()
            ->where('workspace_id', $workspace->id)
            ->with('user:id,name,email')
            ->latest('due_at')
            ->latest()
            ->get()
            ->map(fn (WorkspaceNotification $notification): array => [
                'id' => $notification->id,
                'user_id' => $notification->user_id,
                'title' => $notification->title,
                'message' => $notification->message,
                'category' => $notification->category,
                'tone' => $notification->tone,
                'status' => $notification->status,
                'source_type' => $notification->source_type,
                'source_id' => $notification->source_id,
                'action_url' => $notification->action_url,
                'due_at' => optional($notification->due_at)?->format('Y-m-d\TH:i'),
                'due_at_label' => optional($notification->due_at)?->format('d M Y H:i') ?? 'No due date',
                'read_at' => optional($notification->read_at)?->format('Y-m-d\TH:i'),
                'read_at_label' => optional($notification->read_at)?->format('d M Y H:i') ?? 'Unread',
                'is_read' => $notification->status === 'read',
                'user' => $notification->user ? [
                    'id' => $notification->user->id,
                    'name' => $notification->user->name,
                    'email' => $notification->user->email,
                ] : null,
            ]);

        return $this->appShell(
            workspace: $workspace,
            screen: 'Communication/Notifications',
            title: 'Notifications',
            activeLabel: 'Notifications',
            payload: [
                'notifications' => $notifications,
                'options' => [
                    'users' => User::query()
                        ->orderBy('name')
                        ->get(['id', 'name', 'email'])
                        ->map(fn (User $user): array => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                        ]),
                    'categories' => ['mention', 'task', 'invoice', 'payment', 'lead', 'automation', 'reminder'],
                    'tones' => ['neutral', 'info', 'warning', 'critical'],
                    'statuses' => ['unread', 'read', 'archived'],
                ],
                'summary' => [
                    'total' => $notifications->count(),
                    'unread' => $notifications->where('is_read', false)->count(),
                    'read' => $notifications->where('is_read', true)->count(),
                    'critical' => $notifications->where('tone', 'critical')->count(),
                    'due_today' => $notifications->filter(fn (array $item) => filled($item['due_at']) && str_starts_with($item['due_at_label'], now()->format('d M Y')))->count(),
                ],
            ],
        );
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        WorkspaceNotification::create($this->validateNotification($request, $workspace));

        return back()->with('success', 'Notification created successfully.');
    }

    public function update(Request $request, Workspace $workspace, WorkspaceNotification $notification): RedirectResponse
    {
        abort_unless($notification->workspace_id === $workspace->id, 404);
        $notification->update($this->validateNotification($request, $workspace, $notification));

        return back()->with('success', 'Notification updated successfully.');
    }

    public function destroy(Workspace $workspace, WorkspaceNotification $notification): RedirectResponse
    {
        abort_unless($notification->workspace_id === $workspace->id, 404);
        $notification->delete();

        return back()->with('success', 'Notification deleted successfully.');
    }

    protected function validateNotification(Request $request, Workspace $workspace, ?WorkspaceNotification $notification = null): array
    {
        $validated = $request->validate([
            'user_id' => ['nullable', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'category' => ['required', Rule::in(['mention', 'task', 'invoice', 'payment', 'lead', 'automation', 'reminder'])],
            'tone' => ['required', Rule::in(['neutral', 'info', 'warning', 'critical'])],
            'status' => ['required', Rule::in(['unread', 'read', 'archived'])],
            'source_type' => ['nullable', 'string', 'max:100'],
            'source_id' => ['nullable', 'uuid'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'due_at' => ['nullable', 'date'],
            'read_at' => ['nullable', 'date'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['read_at'] = $validated['status'] === 'read' ? ($validated['read_at'] ?? now()) : null;

        return $validated;
    }
}
