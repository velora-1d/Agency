<?php

namespace App\Services\Project;

use App\Models\ActivityFeed;
use App\Models\Contract;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractService
{
    public function create(Workspace $workspace, array $data): Contract
    {
        $contract = Contract::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $data['client_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'title' => $data['title'],
            'status' => 'draft',
            'content' => $data['content'] ?? null,
            'value' => $data['value'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'reminder_days_before' => $data['reminder_days_before'] ?? 30,
            'esign_token' => Str::random(40),
            'created_by' => Auth::id(),
        ]);

        $this->logActivity($workspace, $contract, sprintf('Kontrak %s berhasil dibuat sebagai Draft.', $contract->title), 'create', 'emerald');

        return $contract;
    }

    public function signDigitally(Contract $contract, string $name, string $ip): Contract
    {
        $contract->update([
            'status' => 'signed',
            'signed_at' => now(),
            'signed_by_name' => $name,
            'signed_ip' => $ip,
        ]);

        $workspace = $contract->workspace; // Assuming relation exists or use id
        
        // Log to Activity Feed if workspace is loaded
        if ($workspace) {
            $this->logActivity($workspace, $contract, sprintf('Kontrak ditandatangani secara digital oleh %s.', $name), 'signed', 'emerald');
        }

        return $contract->refresh();
    }

    public function update(Workspace $workspace, Contract $contract, array $data): Contract
    {
        abort_unless($contract->workspace_id === $workspace->getKey(), 404);

        $contract->update([
            'client_id' => $data['client_id'] ?? $contract->client_id,
            'project_id' => $data['project_id'] ?? $contract->project_id,
            'title' => $data['title'],
            'content' => $data['content'] ?? $contract->content,
            'value' => $data['value'] ?? $contract->value,
            'start_date' => $data['start_date'] ?? $contract->start_date,
            'end_date' => $data['end_date'] ?? $contract->end_date,
            'reminder_days_before' => $data['reminder_days_before'] ?? $contract->reminder_days_before,
        ]);

        $this->logActivity($workspace, $contract, sprintf('Kontrak %s diperbarui.', $contract->title), 'update', 'amber');

        return $contract->refresh();
    }

    public function updateStatus(Workspace $workspace, Contract $contract, string $status): Contract
    {
        abort_unless($contract->workspace_id === $workspace->getKey(), 404);

        $oldStatus = $contract->status;
        $contract->update(['status' => $status]);

        $this->logActivity(
            $workspace, 
            $contract, 
            sprintf('Status kontrak %s diubah dari %s menjadi %s.', $contract->title, $oldStatus, $status), 
            'update', 
            'blue'
        );

        return $contract->refresh();
    }

    public function uploadSignedDocument(Workspace $workspace, Contract $contract, $file): Contract
    {
        abort_unless($contract->workspace_id === $workspace->getKey(), 404);

        if ($contract->signed_file_path) {
            Storage::disk('public')->delete($contract->signed_file_path);
        }

        $path = $file->store('contracts/signed', 'public');

        $contract->update([
            'signed_file_path' => $path,
            'status' => 'signed',
        ]);

        $this->logActivity($workspace, $contract, 'Dokumen kontrak bertanda tangan telah diunggah.', 'upload', 'emerald');

        return $contract->refresh();
    }

    public function delete(Workspace $workspace, Contract $contract): void
    {
        abort_unless($contract->workspace_id === $workspace->getKey(), 404);

        if ($contract->file_path) {
            Storage::disk('public')->delete($contract->file_path);
        }
        if ($contract->signed_file_path) {
            Storage::disk('public')->delete($contract->signed_file_path);
        }

        $title = $contract->title;
        $contract->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'contract',
            'subject_type' => Contract::class,
            'subject_id' => null,
            'description' => sprintf('Kontrak %s telah dihapus.', $title),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
        ]);
    }

    protected function logActivity(
        Workspace $workspace,
        Contract $contract,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'contract',
            'subject_type' => Contract::class,
            'subject_id' => $contract->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'FilePlus',
                    'update' => 'Pencil',
                    'delete' => 'Trash2',
                    'upload' => 'FileUp',
                    default => 'FileText',
                },
                'color' => $color,
            ],
        ]);
    }
}
