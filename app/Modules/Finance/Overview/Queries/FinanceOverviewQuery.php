<?php

namespace App\Modules\Finance\Overview\Queries;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectFinanceSplit;
use App\Models\Transaction;
use App\Models\Workspace;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class FinanceOverviewQuery
{
    public function getOverviewPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $period = $this->resolvePeriod($filters['period']);

        $paidInvoicesInPeriod = $this->paidInvoicesQuery($workspace, $period['start'], $period['end'])->get();
        $issuedInvoicesInPeriod = $this->issuedInvoicesQuery($workspace, $period['start'], $period['end'])->get();
        $manualIncomeTransactions = $this->manualIncomeTransactions($workspace, $period['start'], $period['end'])->get();
        $expenseTransactions = $this->expenseTransactions($workspace, $period['start'], $period['end'])->get();
        $outstandingInvoices = $this->outstandingInvoices($workspace);
        $recentTransactions = $this->recentTransactions($workspace);

        $income = (float) $paidInvoicesInPeriod->sum('total') + (float) $manualIncomeTransactions->sum('amount');
        $expense = (float) $expenseTransactions->sum('amount');
        $profit = $income - $expense;

        return [
            'finance' => [
                'summary' => [
                    'period_label' => $period['label'],
                    'income' => $income,
                    'income_label' => $this->currency($income, $workspace),
                    'expense' => $expense,
                    'expense_label' => $this->currency($expense, $workspace),
                    'profit' => $profit,
                    'profit_label' => $this->currency($profit, $workspace),
                    'outstanding_total' => $outstandingInvoices->sum('outstanding_amount'),
                    'outstanding_total_label' => $this->currency((float) $outstandingInvoices->sum('outstanding_amount'), $workspace),
                    'outstanding_count' => $outstandingInvoices->count(),
                ],
                'cashFlow' => $this->cashFlowSeries($workspace),
                'outstandingInvoices' => $outstandingInvoices->values()->all(),
                'revenueByClient' => $this->revenueByClient($paidInvoicesInPeriod, $workspace),
                'revenueByProject' => $this->revenueByProject($paidInvoicesInPeriod, $workspace),
                'taxSummary' => $this->taxSummary($issuedInvoicesInPeriod, $outstandingInvoices, $workspace),
                'accounts' => $this->accountSnapshot($workspace),
                'financeSplits' => $this->financeSplits($workspace),
                'transactions' => $recentTransactions->values()->all(),
            ],
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($workspace),
        ];
    }

    protected function normalizeFilters(array $input): array
    {
        $period = (string) ($input['period'] ?? 'month');

        if (! in_array($period, ['month', 'quarter', 'year'], true)) {
            $period = 'month';
        }

        return [
            'period' => $period,
        ];
    }

    protected function resolvePeriod(string $period): array
    {
        $now = now();

        return match ($period) {
            'quarter' => [
                'start' => $now->copy()->startOfQuarter(),
                'end' => $now->copy()->endOfQuarter(),
                'label' => 'Quarter to Date',
            ],
            'year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
                'label' => 'Year to Date',
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
                'label' => 'This Month',
            ],
        };
    }

    protected function paidInvoicesQuery(Workspace $workspace, CarbonInterface $start, CarbonInterface $end)
    {
        return Invoice::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->with(['client:id,company_name', 'project:id,name']);
    }

    protected function issuedInvoicesQuery(Workspace $workspace, CarbonInterface $start, CarbonInterface $end)
    {
        return Invoice::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereBetween('created_at', [$start, $end])
            ->with(['client:id,company_name', 'project:id,name']);
    }

    protected function manualIncomeTransactions(Workspace $workspace, CarbonInterface $start, CarbonInterface $end)
    {
        return Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('type', 'income')
            ->whereNull('invoice_id')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
    }

    protected function expenseTransactions(Workspace $workspace, CarbonInterface $start, CarbonInterface $end)
    {
        return Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
    }

    protected function outstandingInvoices(Workspace $workspace): Collection
    {
        return Invoice::query()
            ->where('workspace_id', $workspace->getKey())
            ->whereIn('status', ['draft', 'sent', 'partial', 'overdue'])
            ->with(['client:id,company_name', 'project:id,name'])
            ->orderBy('due_date')
            ->get()
            ->map(function (Invoice $invoice) use ($workspace): array {
                $outstanding = max(0, (float) $invoice->total - (float) $invoice->paid_amount);

                return [
                    'id' => $invoice->getKey(),
                    'number' => $invoice->number,
                    'status' => $invoice->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $invoice->status)),
                    'client' => $invoice->client ? [
                        'id' => $invoice->client->getKey(),
                        'name' => $invoice->client->company_name,
                    ] : null,
                    'project' => $invoice->project ? [
                        'id' => $invoice->project->getKey(),
                        'name' => $invoice->project->name,
                    ] : null,
                    'due_date' => $invoice->due_date?->toDateString(),
                    'due_date_label' => $invoice->due_date?->format('d M Y'),
                    'outstanding_amount' => $outstanding,
                    'outstanding_amount_label' => $this->currency($outstanding, $workspace),
                    'tax_amount' => (float) $invoice->tax_amount,
                    'tax_amount_label' => $this->currency((float) $invoice->tax_amount, $workspace),
                ];
            });
    }

    protected function cashFlowSeries(Workspace $workspace): array
    {
        $labels = [];
        $income = [];
        $expense = [];
        $profit = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $date = now()->subMonths($offset);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $paidInvoices = $this->paidInvoicesQuery($workspace, $start, $end)->get();
            $manualIncome = $this->manualIncomeTransactions($workspace, $start, $end)->sum('amount');
            $expenses = $this->expenseTransactions($workspace, $start, $end)->sum('amount');
            $monthIncome = (float) $paidInvoices->sum('total') + (float) $manualIncome;
            $monthExpense = (float) $expenses;

            $labels[] = $date->format('M Y');
            $income[] = $monthIncome;
            $expense[] = $monthExpense;
            $profit[] = $monthIncome - $monthExpense;
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expense' => $expense,
            'profit' => $profit,
        ];
    }

    protected function revenueByClient(Collection $paidInvoices, Workspace $workspace): array
    {
        return $paidInvoices
            ->groupBy(fn (Invoice $invoice): string => $invoice->client?->company_name ?? 'No Client')
            ->map(fn (Collection $group, string $name): array => [
                'name' => $name,
                'amount' => (float) $group->sum('total'),
                'amount_label' => $this->currency((float) $group->sum('total'), $workspace),
                'count' => $group->count(),
            ])
            ->sortByDesc('amount')
            ->take(5)
            ->values()
            ->all();
    }

    protected function revenueByProject(Collection $paidInvoices, Workspace $workspace): array
    {
        return $paidInvoices
            ->groupBy(fn (Invoice $invoice): string => $invoice->project?->name ?? 'No Project')
            ->map(fn (Collection $group, string $name): array => [
                'name' => $name,
                'amount' => (float) $group->sum('total'),
                'amount_label' => $this->currency((float) $group->sum('total'), $workspace),
                'count' => $group->count(),
            ])
            ->sortByDesc('amount')
            ->take(5)
            ->values()
            ->all();
    }

    protected function taxSummary(Collection $issuedInvoices, Collection $outstandingInvoices, Workspace $workspace): array
    {
        $taxableAmount = (float) $issuedInvoices->sum('subtotal');
        $collectedTax = (float) $issuedInvoices->sum('tax_amount');
        $outstandingTax = (float) $outstandingInvoices->sum('tax_amount');
        $averageRate = (float) $issuedInvoices->avg('tax_rate');

        return [
            'taxable_amount' => $taxableAmount,
            'taxable_amount_label' => $this->currency($taxableAmount, $workspace),
            'collected_tax' => $collectedTax,
            'collected_tax_label' => $this->currency($collectedTax, $workspace),
            'outstanding_tax' => $outstandingTax,
            'outstanding_tax_label' => $this->currency($outstandingTax, $workspace),
            'average_rate' => round($averageRate, 2),
            'average_rate_label' => number_format($averageRate, 2) . '%',
        ];
    }

    protected function accountSnapshot(Workspace $workspace): array
    {
        return BankAccount::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('is_active', true)
            ->orderByDesc('balance')
            ->get()
            ->map(fn (BankAccount $account): array => [
                'id' => $account->getKey(),
                'name' => $account->name,
                'type' => $account->type,
                'balance' => (float) $account->balance,
                'balance_label' => $this->currency((float) $account->balance, $workspace),
            ])
            ->values()
            ->all();
    }

    protected function financeSplits(Workspace $workspace): array
    {
        return ProjectFinanceSplit::query()
            ->whereHas('project', fn ($query) => $query->where('workspace_id', $workspace->getKey()))
            ->with('project:id,name')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (ProjectFinanceSplit $split): array => [
                'id' => $split->getKey(),
                'project' => $split->project ? [
                    'id' => $split->project->getKey(),
                    'name' => $split->project->name,
                ] : null,
                'template_name' => $split->template_name,
                'total_project_value_label' => $this->currency((float) $split->total_project_value, $workspace),
                'total_operational_cost_label' => $this->currency((float) $split->total_operational_cost, $workspace),
                'total_team_fee_label' => $this->currency((float) $split->total_team_fee, $workspace),
                'total_kas_kantor_label' => $this->currency((float) $split->total_kas_kantor, $workspace),
            ])
            ->values()
            ->all();
    }

    protected function recentTransactions(Workspace $workspace): Collection
    {
        return Transaction::query()
            ->where('workspace_id', $workspace->getKey())
            ->with(['account:id,name', 'project:id,name', 'invoice:id,number'])
            ->latest('date')
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn (Transaction $transaction): array => [
                'id' => $transaction->getKey(),
                'account_id' => $transaction->account_id,
                'invoice_id' => $transaction->invoice_id,
                'project_id' => $transaction->project_id,
                'type' => $transaction->type,
                'type_label' => ucfirst($transaction->type),
                'category' => $transaction->category,
                'amount' => (float) $transaction->amount,
                'amount_label' => $this->currency((float) $transaction->amount, $workspace),
                'description' => $transaction->description,
                'date' => $transaction->date?->toDateString(),
                'date_label' => $transaction->date?->format('d M Y'),
                'account' => $transaction->account ? [
                    'id' => $transaction->account->getKey(),
                    'name' => $transaction->account->name,
                ] : null,
                'project' => $transaction->project ? [
                    'id' => $transaction->project->getKey(),
                    'name' => $transaction->project->name,
                ] : null,
                'invoice' => $transaction->invoice ? [
                    'id' => $transaction->invoice->getKey(),
                    'number' => $transaction->invoice->number,
                ] : null,
            ]);
    }

    protected function filterOptions(Workspace $workspace): array
    {
        return [
            'periods' => [
                ['value' => 'month', 'label' => 'This Month'],
                ['value' => 'quarter', 'label' => 'This Quarter'],
                ['value' => 'year', 'label' => 'This Year'],
            ],
            'accounts' => BankAccount::query()
                ->where('workspace_id', $workspace->getKey())
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (BankAccount $account): array => [
                    'id' => $account->getKey(),
                    'name' => $account->name,
                ])
                ->values()
                ->all(),
            'projects' => Project::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Project $project): array => [
                    'id' => $project->getKey(),
                    'name' => $project->name,
                ])
                ->values()
                ->all(),
            'invoices' => Invoice::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderByDesc('created_at')
                ->limit(20)
                ->get(['id', 'number'])
                ->map(fn (Invoice $invoice): array => [
                    'id' => $invoice->getKey(),
                    'name' => $invoice->number,
                ])
                ->values()
                ->all(),
            'transactionTypes' => [
                ['value' => 'income', 'label' => 'Income'],
                ['value' => 'expense', 'label' => 'Expense'],
            ],
            'clients' => Client::query()
                ->where('workspace_id', $workspace->getKey())
                ->orderBy('company_name')
                ->get(['id', 'company_name'])
                ->map(fn (Client $client): array => [
                    'id' => $client->getKey(),
                    'name' => $client->company_name,
                ])
                ->values()
                ->all(),
        ];
    }

    protected function currency(float $amount, Workspace $workspace): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR');
    }
}
