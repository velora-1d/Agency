<?php

namespace App\Http\Controllers\App\Communication;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Message;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Response;

class InboxController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace): Response
    {
        $conversations = Conversation::query()
            ->where('workspace_id', $workspace->id)
            ->with([
                'client:id,company_name',
                'lead:id,name',
                'assignee:id,name',
            ])
            ->latest('last_message_at')
            ->latest()
            ->limit(36)
            ->get()
            ->map(function (Conversation $conversation): array {
                $messages = $conversation->messages()
                    ->with('sender:id,name')
                    ->latest()
                    ->limit(12)
                    ->get()
                    ->reverse()
                    ->values()
                    ->map(fn (Message $message): array => [
                        'id' => $message->id,
                        'content' => $message->content,
                        'type' => $message->type,
                        'file_path' => $message->file_path,
                        'wa_message_id' => $message->wa_message_id,
                        'is_from_client' => $message->is_from_client,
                        'read_at_label' => optional($message->read_at)?->format('d M Y H:i') ?? 'Unread',
                        'created_at_label' => optional($message->created_at)?->format('d M Y H:i') ?? '-',
                        'sender' => $message->sender ? [
                            'id' => $message->sender->id,
                            'name' => $message->sender->name,
                        ] : null,
                    ]);

                $unreadCount = $messages->where('read_at_label', 'Unread')->count();
                $latestMessage = $messages->last();

                return [
                    'id' => $conversation->id,
                    'type' => $conversation->type,
                    'name' => $conversation->name,
                    'wa_contact_phone' => $conversation->wa_contact_phone,
                    'wa_contact_name' => $conversation->wa_contact_name,
                    'client_id' => $conversation->client_id,
                    'lead_id' => $conversation->lead_id,
                    'assigned_to' => $conversation->assigned_to,
                    'status' => $conversation->status,
                    'label' => $conversation->label,
                    'last_message_at_label' => optional($conversation->last_message_at)?->format('d M Y H:i') ?? 'No messages yet',
                    'client' => $conversation->client ? [
                        'id' => $conversation->client->id,
                        'name' => $conversation->client->company_name,
                    ] : null,
                    'lead' => $conversation->lead ? [
                        'id' => $conversation->lead->id,
                        'name' => $conversation->lead->name,
                    ] : null,
                    'assignee' => $conversation->assignee ? [
                        'id' => $conversation->assignee->id,
                        'name' => $conversation->assignee->name,
                    ] : null,
                    'message_count' => $messages->count(),
                    'unread_count' => $unreadCount,
                    'latest_preview' => $latestMessage['content'] ?? 'No messages yet',
                    'messages' => $messages,
                ];
            });

        $users = $workspace->users()
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

        $clients = Client::query()
            ->where('workspace_id', $workspace->id)
            ->orderBy('company_name')
            ->get(['id', 'company_name'])
            ->map(fn (Client $client): array => [
                'id' => $client->id,
                'name' => $client->company_name,
            ]);

        $leads = Lead::query()
            ->where('workspace_id', $workspace->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Lead $lead): array => [
                'id' => $lead->id,
                'name' => $lead->name,
            ]);

        return $this->appShell(
            workspace: $workspace,
            screen: 'Communication/Inbox',
            title: 'Inbox',
            activeLabel: 'Inbox',
            payload: [
                'conversations' => $conversations,
                'options' => [
                    'users' => $users,
                    'clients' => $clients,
                    'leads' => $leads,
                    'types' => ['internal', 'whatsapp'],
                    'statuses' => ['open', 'pending', 'resolved'],
                    'labels' => ['lead', 'client', 'support', 'project', 'finance'],
                ],
                'summary' => [
                    'total' => $conversations->count(),
                    'internal' => $conversations->where('type', 'internal')->count(),
                    'whatsapp' => $conversations->where('type', 'whatsapp')->count(),
                    'open' => $conversations->where('status', 'open')->count(),
                    'pending' => $conversations->where('status', 'pending')->count(),
                    'resolved' => $conversations->where('status', 'resolved')->count(),
                    'unread' => $conversations->sum('unread_count'),
                ],
            ],
        );
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $validated = $this->validateConversation($request, $workspace);
        $conversation = Conversation::create($validated);

        if (filled($request->string('initial_message')->toString())) {
            $conversation->messages()->create([
                'sender_id' => $request->user()?->id,
                'content' => $request->string('initial_message')->toString(),
                'type' => 'text',
                'is_from_client' => false,
                'read_at' => now(),
            ]);
            $conversation->update(['last_message_at' => now()]);
        }

        return back()->with('success', 'Conversation created successfully.');
    }

    public function update(Request $request, Workspace $workspace, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->workspace_id === $workspace->id, 404);
        $conversation->update($this->validateConversation($request, $workspace, $conversation));

        return back()->with('success', 'Conversation updated successfully.');
    }

    public function destroy(Workspace $workspace, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->workspace_id === $workspace->id, 404);
        $conversation->delete();

        return back()->with('success', 'Conversation deleted successfully.');
    }

    public function storeMessage(Request $request, Workspace $workspace, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->workspace_id === $workspace->id, 404);

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'type' => ['nullable', 'string', 'max:20'],
            'is_from_client' => ['nullable', 'boolean'],
        ]);

        $conversation->messages()->create([
            'sender_id' => $request->user()?->id,
            'content' => $validated['content'],
            'type' => $validated['type'] ?? 'text',
            'is_from_client' => $request->boolean('is_from_client'),
            'read_at' => $request->boolean('is_from_client') ? null : now(),
        ]);

        $conversation->update(['last_message_at' => now()]);

        return back()->with('success', 'Message sent successfully.');
    }

    protected function validateConversation(Request $request, Workspace $workspace, ?Conversation $conversation = null): array
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['internal', 'whatsapp'])],
            'name' => ['nullable', 'string', 'max:100'],
            'wa_contact_phone' => ['nullable', 'string', 'max:20'],
            'wa_contact_name' => ['nullable', 'string', 'max:100'],
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
            ],
            'lead_id' => [
                'nullable',
                Rule::exists('leads', 'id')->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
            ],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')],
            'status' => ['required', Rule::in(['open', 'pending', 'resolved'])],
            'label' => ['nullable', 'string', 'max:50'],
        ]);

        $validated['workspace_id'] = $workspace->id;

        return $validated;
    }
}
