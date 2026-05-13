<?php

namespace App\Services\Finance;

use App\Models\ActivityFeed;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashBankService
{
    public function createAccount(Workspace $workspace, array $data): BankAccount
    {
        return DB::transaction(function () use ($workspace, $data): BankAccount {
            $account = BankAccount::query()->create([
                'workspace_id' => $workspace->getKey(),
                'name' => $data['name'],
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'account_holder' => $data['account_holder'] ?? null,
                'type' => $data['type'],
                'balance' => 0,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            if ((float) ($data['opening_balance'] ?? 0) > 0) {
                Transaction::query()->create([
                    'workspace_id' => $workspace->getKey(),
                    'account_id' => $account->getKey(),
                    'type' => 'income',
                    'category' => 'opening_balance',
                    'amount' => $data['opening_balance'],
                    'description' => 'Opening balance',
                    'date' => now()->toDateString(),
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);
            }

            $this->logActivity($workspace, $account, sprintf('Account %s berhasil dibuat.', $account->name), 'create', 'emerald');

            return $account->refresh();
        });
    }

    public function updateAccount(Workspace $workspace, BankAccount $account, array $data): BankAccount
    {
        abort_unless($account->workspace_id === $workspace->getKey(), 404);

        $account->update([
            'name' => $data['name'],
            'bank_name' => $data['bank_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'account_holder' => $data['account_holder'] ?? null,
            'type' => $data['type'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $this->logActivity($workspace, $account, sprintf('Account %s diperbarui.', $account->name), 'update', 'amber');

        return $account->refresh();
    }

    public function deleteAccount(Workspace $workspace, BankAccount $account): void
    {
        abort_unless($account->workspace_id === $workspace->getKey(), 404);

        $name = $account->name;
        $account->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => BankAccount::class,
            'subject_id' => null,
            'description' => sprintf('Account %s dihapus.', $name),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    public function transfer(Workspace $workspace, array $data): void
    {
        DB::transaction(function () use ($workspace, $data): void {
            $from = $this->resolveAccount($workspace, $data['from_account_id']);
            $to = $this->resolveAccount($workspace, $data['to_account_id']);
            $description = $data['description'] ?? sprintf('Transfer from %s to %s', $from->name, $to->name);

            Transaction::query()->create([
                'workspace_id' => $workspace->getKey(),
                'account_id' => $from->getKey(),
                'type' => 'expense',
                'category' => 'transfer_out',
                'amount' => $data['amount'],
                'description' => $description,
                'date' => $data['date'],
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            Transaction::query()->create([
                'workspace_id' => $workspace->getKey(),
                'account_id' => $to->getKey(),
                'type' => 'income',
                'category' => 'transfer_in',
                'amount' => $data['amount'],
                'description' => $description,
                'date' => $data['date'],
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            $this->logTransferActivity($workspace, $from, $to, (float) $data['amount']);
        });
    }

    public function reconcile(Workspace $workspace, BankAccount $account, array $data): BankAccount
    {
        abort_unless($account->workspace_id === $workspace->getKey(), 404);

        $account->update([
            'last_reconciled_at' => $data['last_reconciled_at'],
            'reconciliation_notes' => $data['reconciliation_notes'] ?? null,
        ]);

        $this->logActivity($workspace, $account, sprintf('Account %s direkonsiliasi.', $account->name), 'reconcile', 'sky');

        return $account->refresh();
    }

    protected function resolveAccount(Workspace $workspace, string $accountId): BankAccount
    {
        return BankAccount::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($accountId);
    }

    protected function logActivity(
        Workspace $workspace,
        BankAccount $account,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => BankAccount::class,
            'subject_id' => $account->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'Landmark',
                    'update' => 'Pencil',
                    default => 'RefreshCw',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }

    protected function logTransferActivity(
        Workspace $workspace,
        BankAccount $from,
        BankAccount $to,
        float $amount
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => BankAccount::class,
            'subject_id' => $from->getKey(),
            'description' => sprintf(
                'Transfer %s dari %s ke %s.',
                number_format($amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR'),
                $from->name,
                $to->name
            ),
            'metadata' => [
                'action' => 'transfer',
                'icon' => 'ArrowLeftRight',
                'color' => 'sky',
            ],
            'created_at' => now(),
        ]);
    }
}
