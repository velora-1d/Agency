<?php

namespace App\Services\CRM;

use App\Models\ActivityFeed;
use App\Models\Client;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClientService
{
    public function create(Workspace $workspace, array $data): Client
    {
        $client = Client::query()->create([
            'workspace_id' => $workspace->getKey(),
            'company_name' => $data['company_name'],
            'pic_name' => $data['pic_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'industry' => $data['industry'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'province' => $data['province'] ?? null,
            'status' => $data['status'],
            'assigned_to' => $this->resolveAssignee($workspace, $data['assigned_to'] ?? null),
            'portal_access' => (bool) ($data['portal_access'] ?? false),
            'portal_token' => ! empty($data['portal_access']) ? Str::random(40) : null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logActivity($workspace, $client, sprintf('Client %s berhasil dibuat.', $client->company_name), 'create', 'emerald');

        return $client;
    }

    public function update(Workspace $workspace, Client $client, array $data): Client
    {
        abort_unless($client->workspace_id === $workspace->getKey(), 404);

        $portalAccess = (bool) ($data['portal_access'] ?? false);

        $client->update([
            'company_name' => $data['company_name'],
            'pic_name' => $data['pic_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'industry' => $data['industry'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'province' => $data['province'] ?? null,
            'status' => $data['status'],
            'assigned_to' => $this->resolveAssignee($workspace, $data['assigned_to'] ?? null),
            'portal_access' => $portalAccess,
            'portal_token' => $portalAccess ? ($client->portal_token ?: Str::random(40)) : null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logActivity($workspace, $client, sprintf('Client %s diperbarui.', $client->company_name), 'update', 'amber');

        return $client->refresh();
    }

    public function updateNotes(Workspace $workspace, Client $client, ?string $notes): Client
    {
        abort_unless($client->workspace_id === $workspace->getKey(), 404);

        $client->update([
            'notes' => $notes,
        ]);

        $this->logActivity($workspace, $client, 'Notes client diperbarui.', 'note', 'amber');

        return $client->refresh();
    }

    public function addActivity(Workspace $workspace, Client $client, string $content): ActivityFeed
    {
        abort_unless($client->workspace_id === $workspace->getKey(), 404);

        return $this->logActivity($workspace, $client, $content, 'note', 'amber');
    }

    public function delete(Workspace $workspace, Client $client): void
    {
        abort_unless($client->workspace_id === $workspace->getKey(), 404);

        $companyName = $client->company_name;
        $client->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'client',
            'subject_type' => Client::class,
            'subject_id' => null,
            'description' => sprintf('Client %s dihapus.', $companyName),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
        ]);
    }

    protected function resolveAssignee(Workspace $workspace, ?string $assigneeId): ?string
    {
        if (! $assigneeId) {
            return null;
        }

        $exists = $workspace->users()->where('users.id', $assigneeId)->exists();
        abort_unless($exists, 422);

        return $assigneeId;
    }

    protected function logActivity(
        Workspace $workspace,
        Client $client,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'client',
            'subject_type' => Client::class,
            'subject_id' => $client->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'CirclePlus',
                    'update' => 'Pencil',
                    'delete' => 'Trash2',
                    default => 'NotebookPen',
                },
                'color' => $color,
            ],
        ]);
    }
}
