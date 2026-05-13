<?php

namespace App\Services\Finance;

use App\Models\ActivityFeed;
use App\Models\ChartOfAccount;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class FinancialReportService
{
    public function createChartOfAccount(Workspace $workspace, array $data): ChartOfAccount
    {
        $account = ChartOfAccount::query()->create([
            'workspace_id' => $workspace->getKey(),
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'type' => $data['type'],
            'category' => $data['category'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logActivity($workspace, $account, sprintf('COA %s berhasil dibuat.', $account->code), 'create', 'emerald');

        return $account->refresh();
    }

    public function updateChartOfAccount(Workspace $workspace, ChartOfAccount $account, array $data): ChartOfAccount
    {
        abort_unless($account->workspace_id === $workspace->getKey(), 404);

        $account->update([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'type' => $data['type'],
            'category' => $data['category'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logActivity($workspace, $account, sprintf('COA %s diperbarui.', $account->code), 'update', 'amber');

        return $account->refresh();
    }

    public function deleteChartOfAccount(Workspace $workspace, ChartOfAccount $account): void
    {
        abort_unless($account->workspace_id === $workspace->getKey(), 404);

        $code = $account->code;
        $account->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => ChartOfAccount::class,
            'subject_id' => null,
            'description' => sprintf('COA %s dihapus.', $code),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    protected function logActivity(Workspace $workspace, ChartOfAccount $account, string $description, string $action, string $color): ActivityFeed
    {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => ChartOfAccount::class,
            'subject_id' => $account->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'BookPlus',
                    'update' => 'Pencil',
                    default => 'ReceiptText',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }
}
