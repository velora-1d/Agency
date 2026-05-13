<?php

namespace App\Modules\Finance\CashBank\Queries;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CashBankIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $accounts = $this->accountQuery($workspace, $filters)->get();

        return [
            'accounts' => [
                'summary' => $this->buildSummary($accounts, $workspace),
                'items' => $accounts->map(fn (BankAccount $account): array => $this->transformAccount($account, $workspace))->all(),
                'selected_id' => $filters['account'],
            ],
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($workspace),
        ];
    }

    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'search' => $normalize($input['search'] ?? null),
            'type' => $normalize($input['type'] ?? null),
            'active' => $normalize($input['active'] ?? null),
            'account' => $normalize($input['account'] ?? null),
        ];
    }

    protected function accountQuery(Workspace $workspace, array $filters): Builder
    {
        return BankAccount::query()
            ->where('workspace_id', $workspace->getKey())
            ->with(['transactions' => fn ($query) => $query->latest('date')->latest('created_at')->limit(10)])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('bank_name', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhere('account_holder', 'like', "%{$search}%");
                });
            })
            ->when($filters['type'], fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['active'], function (Builder $query, string $active): void {
                $query->where('is_active', $active === 'yes');
            })
            ->orderByDesc('is_active')
            ->orderBy('name');
    }

    protected function buildSummary(Collection $accounts, Workspace $workspace): array
    {
        $bankBalance = (float) $accounts->where('type', 'bank')->sum('balance');
        $cashBalance = (float) $accounts->where('type', 'cash')->sum('balance');
        $transferTotal = (float) Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('category', 'transfer_out')
            ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('amount');

        return [
            'total_accounts' => $accounts->count(),
            'active_accounts' => $accounts->where('is_active', true)->count(),
            'bank_balance_label' => $this->currency($bankBalance, $workspace),
            'cash_balance_label' => $this->currency($cashBalance, $workspace),
            'combined_balance_label' => $this->currency($bankBalance + $cashBalance + (float) $accounts->where('type', 'e-wallet')->sum('balance'), $workspace),
            'transfers_this_month_label' => $this->currency($transferTotal, $workspace),
        ];
    }

    protected function transformAccount(BankAccount $account, Workspace $workspace): array
    {
        return [
            'id' => $account->getKey(),
            'name' => $account->name,
            'bank_name' => $account->bank_name,
            'account_number' => $account->account_number,
            'account_holder' => $account->account_holder,
            'type' => $account->type,
            'type_label' => strtoupper($account->type === 'e-wallet' ? 'e-wallet' : $account->type),
            'balance' => (float) $account->balance,
            'balance_label' => $this->currency((float) $account->balance, $workspace),
            'is_active' => (bool) $account->is_active,
            'is_active_label' => $account->is_active ? 'Active' : 'Inactive',
            'last_reconciled_at' => $account->last_reconciled_at?->toDateString(),
            'last_reconciled_at_label' => $account->last_reconciled_at?->format('d M Y H:i'),
            'reconciliation_notes' => $account->reconciliation_notes,
            'transactions' => $account->transactions->map(fn (Transaction $transaction): array => [
                'id' => $transaction->getKey(),
                'type' => $transaction->type,
                'type_label' => ucfirst($transaction->type),
                'category' => $transaction->category,
                'amount' => (float) $transaction->amount,
                'amount_label' => $this->currency((float) $transaction->amount, $workspace),
                'description' => $transaction->description,
                'date_label' => $transaction->date?->format('d M Y'),
            ])->values()->all(),
        ];
    }

    protected function filterOptions(Workspace $workspace): array
    {
        return [
            'accounts' => BankAccount::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (BankAccount $account): array => [
                    'id' => $account->getKey(),
                    'name' => $account->name,
                ])->values()->all(),
            'types' => [
                ['value' => 'bank', 'label' => 'Bank'],
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'e-wallet', 'label' => 'E-Wallet'],
            ],
            'activeStates' => [
                ['value' => 'yes', 'label' => 'Active'],
                ['value' => 'no', 'label' => 'Inactive'],
            ],
        ];
    }

    protected function currency(float $amount, Workspace $workspace): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR');
    }
}
