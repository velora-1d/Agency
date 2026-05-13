<?php

namespace App\Modules\Finance\Transactions\Queries;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TransactionIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $transactions = $this->transactionQuery($workspace, $filters)->get();

        return [
            'transactions' => [
                'summary' => $this->buildSummary($transactions, $workspace),
                'items' => $transactions->map(fn (Transaction $transaction): array => $this->transformTransaction($transaction, $workspace))->values()->all(),
                'selected_id' => $filters['transaction'],
                'category_breakdown' => $this->categoryBreakdown($transactions, $workspace),
            ],
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($workspace, $transactions),
        ];
    }

    public function exportRows(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);

        return $this->transactionQuery($workspace, $filters)
            ->get()
            ->map(fn (Transaction $transaction): array => [
                'date' => $transaction->date?->toDateString(),
                'type' => $this->typeLabel($transaction->type),
                'category' => $transaction->category ?: 'General',
                'amount' => (float) $transaction->amount,
                'client' => $this->clientName($transaction) ?: 'No client',
                'project' => $transaction->project?->name ?: 'No project',
                'invoice' => $transaction->invoice?->number ?: 'Manual entry',
                'account' => $transaction->account?->name ?: 'No account',
                'entry_mode' => $transaction->invoice_id ? 'Invoice linked' : 'Manual entry',
                'attachment' => $transaction->attachment_path ?: '',
                'description' => $transaction->description ?: '',
            ])->all();
    }

    protected function normalizeFilters(array $input): array
    {
        $normalize = static fn (mixed $value): ?string => filled($value) ? (string) $value : null;

        return [
            'search' => $normalize($input['search'] ?? null),
            'type' => $normalize($input['type'] ?? null),
            'category' => $normalize($input['category'] ?? null),
            'client' => $normalize($input['client'] ?? null),
            'project' => $normalize($input['project'] ?? null),
            'invoice' => $normalize($input['invoice'] ?? null),
            'account' => $normalize($input['account'] ?? null),
            'date_from' => $normalize($input['date_from'] ?? null),
            'date_to' => $normalize($input['date_to'] ?? null),
            'transaction' => $normalize($input['transaction'] ?? null),
        ];
    }

    protected function transactionQuery(Workspace $workspace, array $filters): Builder
    {
        return Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'account:id,name,type',
                'invoice:id,number,client_id,project_id',
                'invoice.client:id,company_name,pic_name',
                'project:id,name,client_id',
                'project.client:id,company_name,pic_name',
            ])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('invoice', fn (Builder $invoiceQuery) => $invoiceQuery->where('number', 'like', "%{$search}%"))
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('invoice.client', fn (Builder $clientQuery) => $clientQuery->where('company_name', 'like', "%{$search}%"))
                        ->orWhereHas('project.client', fn (Builder $clientQuery) => $clientQuery->where('company_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['type'], fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['category'], fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters['project'], fn (Builder $query, string $projectId) => $query->where('project_id', $projectId))
            ->when($filters['invoice'], fn (Builder $query, string $invoiceId) => $query->where('invoice_id', $invoiceId))
            ->when($filters['account'], fn (Builder $query, string $accountId) => $query->where('account_id', $accountId))
            ->when($filters['client'], function (Builder $query, string $clientId): void {
                $query->where(function (Builder $builder) use ($clientId): void {
                    $builder
                        ->whereHas('invoice', fn (Builder $invoiceQuery) => $invoiceQuery->where('client_id', $clientId))
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery->where('client_id', $clientId));
                });
            })
            ->when($filters['date_from'], fn (Builder $query, string $dateFrom) => $query->whereDate('date', '>=', $dateFrom))
            ->when($filters['date_to'], fn (Builder $query, string $dateTo) => $query->whereDate('date', '<=', $dateTo))
            ->orderByDesc('date')
            ->orderByDesc('created_at');
    }

    protected function buildSummary(Collection $transactions, Workspace $workspace): array
    {
        $income = (float) $transactions->where('type', 'income')->sum('amount');
        $expense = (float) $transactions->where('type', 'expense')->sum('amount');
        $invoiceLinked = $transactions->whereNotNull('invoice_id')->count();
        $manualEntries = $transactions->whereNull('invoice_id')->count();

        return [
            'total_transactions' => $transactions->count(),
            'income_total' => $income,
            'income_total_label' => $this->currency($income, $workspace),
            'expense_total' => $expense,
            'expense_total_label' => $this->currency($expense, $workspace),
            'net_total' => $income - $expense,
            'net_total_label' => $this->currency($income - $expense, $workspace),
            'invoice_linked_count' => $invoiceLinked,
            'manual_entry_count' => $manualEntries,
            'attachment_count' => $transactions->filter(fn (Transaction $transaction): bool => filled($transaction->attachment_path))->count(),
            'latest_transaction_label' => $transactions->first()?->date?->format('d M Y') ?? 'No transactions yet',
        ];
    }

    protected function categoryBreakdown(Collection $transactions, Workspace $workspace): array
    {
        return $transactions
            ->groupBy(fn (Transaction $transaction): string => $transaction->category ?: 'general')
            ->map(fn (Collection $group, string $category): array => [
                'category' => $category,
                'label' => ucwords(str_replace(['-', '_'], ' ', $category)),
                'count' => $group->count(),
                'amount' => (float) $group->sum('amount'),
                'amount_label' => $this->currency((float) $group->sum('amount'), $workspace),
            ])
            ->sortByDesc('amount')
            ->take(6)
            ->values()
            ->all();
    }

    protected function filterOptions(Workspace $workspace, Collection $transactions): array
    {
        $defaultCategories = collect([
            'operasional',
            'gaji',
            'tools',
            'ads',
            'retainer',
            'invoice_payment',
            'manual_income',
            'reimbursement',
        ]);

        $dbCategories = Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereNotNull('category')
            ->pluck('category');

        return [
            'types' => [
                ['value' => 'income', 'label' => 'Income'],
                ['value' => 'expense', 'label' => 'Expense'],
            ],
            'categories' => $defaultCategories
                ->merge($dbCategories)
                ->filter()
                ->unique()
                ->values()
                ->map(fn (string $category): array => [
                    'value' => $category,
                    'label' => ucwords(str_replace(['-', '_'], ' ', $category)),
                ])->all(),
            'clients' => Client::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('company_name')
                ->get(['id', 'company_name'])
                ->map(fn (Client $client): array => [
                    'id' => $client->getKey(),
                    'name' => $client->company_name,
                ])->values()->all(),
            'projects' => Project::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Project $project): array => [
                    'id' => $project->getKey(),
                    'name' => $project->name,
                ])->values()->all(),
            'invoices' => Invoice::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderByDesc('created_at')
                ->get(['id', 'number'])
                ->map(fn (Invoice $invoice): array => [
                    'id' => $invoice->getKey(),
                    'name' => $invoice->number,
                ])->values()->all(),
            'accounts' => BankAccount::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (BankAccount $account): array => [
                    'id' => $account->getKey(),
                    'name' => $account->name,
                ])->values()->all(),
            'entryModes' => [
                ['value' => 'manual', 'label' => 'Manual Entry'],
                ['value' => 'invoice_linked', 'label' => 'Invoice Linked'],
            ],
        ];
    }

    protected function transformTransaction(Transaction $transaction, Workspace $workspace): array
    {
        $clientName = $this->clientName($transaction);

        return [
            'id' => $transaction->getKey(),
            'account_id' => $transaction->account_id,
            'invoice_id' => $transaction->invoice_id,
            'project_id' => $transaction->project_id,
            'type' => $transaction->type,
            'type_label' => $this->typeLabel($transaction->type),
            'category' => $transaction->category,
            'category_label' => ucwords(str_replace(['-', '_'], ' ', $transaction->category ?: 'general')),
            'amount' => (float) $transaction->amount,
            'amount_label' => $this->currency((float) $transaction->amount, $workspace),
            'description' => $transaction->description,
            'attachment_path' => $transaction->attachment_path,
            'has_attachment' => filled($transaction->attachment_path),
            'date' => $transaction->date?->toDateString(),
            'date_label' => $transaction->date?->format('d M Y'),
            'entry_mode' => $transaction->invoice_id ? 'invoice_linked' : 'manual',
            'entry_mode_label' => $transaction->invoice_id ? 'Invoice Linked' : 'Manual Entry',
            'account' => $transaction->account ? [
                'id' => $transaction->account->getKey(),
                'name' => $transaction->account->name,
                'type' => $transaction->account->type,
            ] : null,
            'invoice' => $transaction->invoice ? [
                'id' => $transaction->invoice->getKey(),
                'number' => $transaction->invoice->number,
            ] : null,
            'project' => $transaction->project ? [
                'id' => $transaction->project->getKey(),
                'name' => $transaction->project->name,
            ] : null,
            'client' => $clientName ? [
                'name' => $clientName,
            ] : null,
        ];
    }

    protected function clientName(Transaction $transaction): ?string
    {
        return $transaction->invoice?->client?->company_name
            ?? $transaction->project?->client?->company_name;
    }

    protected function typeLabel(string $type): string
    {
        return $type === 'income' ? 'Income' : 'Expense';
    }

    protected function currency(float $amount, Workspace $workspace): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR');
    }
}
