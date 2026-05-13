<?php

namespace App\Services\Finance;

use App\Models\ActivityFeed;
use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    public function create(Workspace $workspace, array $data): Transaction
    {
        $account = $this->resolveAccount($workspace, $data['account_id'] ?? null);
        $invoice = $this->resolveInvoice($workspace, $data['invoice_id'] ?? null);
        $project = $this->resolveProject($workspace, $data['project_id'] ?? $invoice?->project_id);

        $transaction = Transaction::query()->create([
            'workspace_id' => $workspace->getKey(),
            'account_id' => $account?->getKey(),
            'invoice_id' => $invoice?->getKey(),
            'project_id' => $project?->getKey(),
            'type' => $data['type'],
            'category' => $data['category'] ?? null,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'attachment_path' => $data['attachment_path'] ?? null,
            'date' => $data['date'],
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        $this->logActivity($workspace, $transaction, sprintf('Transaksi %s berhasil dibuat.', $transaction->category ?: $transaction->type), 'create', 'emerald');

        return $transaction->refresh()->load(['account', 'invoice', 'project']);
    }

    public function update(Workspace $workspace, Transaction $transaction, array $data): Transaction
    {
        abort_unless($transaction->workspace_id === $workspace->getKey(), 404);

        $account = $this->resolveAccount($workspace, $data['account_id'] ?? null);
        $invoice = $this->resolveInvoice($workspace, $data['invoice_id'] ?? null);
        $project = $this->resolveProject($workspace, $data['project_id'] ?? $invoice?->project_id);

        $transaction->update([
            'account_id' => $account?->getKey(),
            'invoice_id' => $invoice?->getKey(),
            'project_id' => $project?->getKey(),
            'type' => $data['type'],
            'category' => $data['category'] ?? null,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'attachment_path' => $data['attachment_path'] ?? null,
            'date' => $data['date'],
        ]);

        $this->logActivity($workspace, $transaction, sprintf('Transaksi %s diperbarui.', $transaction->category ?: $transaction->type), 'update', 'amber');

        return $transaction->refresh()->load(['account', 'invoice', 'project']);
    }

    public function delete(Workspace $workspace, Transaction $transaction): void
    {
        abort_unless($transaction->workspace_id === $workspace->getKey(), 404);

        $label = $transaction->category ?: $transaction->type;
        $transaction->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Transaction::class,
            'subject_id' => null,
            'description' => sprintf('Transaksi %s dihapus.', $label),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    protected function resolveAccount(Workspace $workspace, ?string $accountId): ?BankAccount
    {
        if (! $accountId) {
            return null;
        }

        return BankAccount::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($accountId);
    }

    protected function resolveInvoice(Workspace $workspace, ?string $invoiceId): ?Invoice
    {
        if (! $invoiceId) {
            return null;
        }

        return Invoice::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($invoiceId);
    }

    protected function resolveProject(Workspace $workspace, ?string $projectId): ?Project
    {
        if (! $projectId) {
            return null;
        }

        return Project::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($projectId);
    }

    protected function logActivity(
        Workspace $workspace,
        Transaction $transaction,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Transaction::class,
            'subject_id' => $transaction->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'Wallet',
                    'update' => 'Pencil',
                    default => 'Banknote',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }
}
