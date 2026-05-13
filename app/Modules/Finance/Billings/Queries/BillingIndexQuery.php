<?php

namespace App\Modules\Finance\Billings\Queries;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BillingIndexQuery
{
    public function getIndexPayload(Workspace $workspace, array $input = []): array
    {
        $filters = $this->normalizeFilters($input);
        $billings = $this->applyCollectionFilters(
            $this->billingQuery($workspace, $filters)->get(),
            $filters,
        )->values();

        return [
            'billings' => [
                'summary' => $this->buildSummary($billings, $workspace),
                'items' => $billings->map(fn (Billing $billing): array => $this->transformBilling($billing, $workspace))->all(),
                'selected_id' => $filters['billing'],
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
            'client' => $normalize($input['client'] ?? null),
            'project' => $normalize($input['project'] ?? null),
            'type' => $normalize($input['type'] ?? null),
            'status' => $normalize($input['status'] ?? null),
            'billing_cycle' => $normalize($input['billing_cycle'] ?? null),
            'schedule_state' => $normalize($input['schedule_state'] ?? null),
            'billing' => $normalize($input['billing'] ?? null),
        ];
    }

    protected function billingQuery(Workspace $workspace, array $filters): Builder
    {
        return Billing::query()
            ->where('workspace_id', $workspace->getKey())
            ->with([
                'client:id,company_name,pic_name,email,phone',
                'project:id,name',
                'invoices' => fn ($query) => $query
                    ->with(['payments.verifier:id,name'])
                    ->latest('created_at'),
            ])
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('client', fn (Builder $clientQuery) => $clientQuery->where('company_name', 'like', "%{$search}%"))
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('invoices', fn (Builder $invoiceQuery) => $invoiceQuery->where('number', 'like', "%{$search}%"));
                });
            })
            ->when($filters['client'], fn (Builder $query, string $clientId) => $query->where('client_id', $clientId))
            ->when($filters['project'], fn (Builder $query, string $projectId) => $query->where('project_id', $projectId))
            ->when($filters['type'], fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['billing_cycle'], fn (Builder $query, string $cycle) => $query->where('billing_cycle', $cycle))
            ->orderByRaw("CASE WHEN status = 'paused' THEN 1 ELSE 0 END")
            ->orderByRaw("CASE WHEN next_invoice_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('next_invoice_date')
            ->orderByDesc('updated_at');
    }

    protected function applyCollectionFilters(Collection $billings, array $filters): Collection
    {
        if (! $filters['schedule_state']) {
            return $billings;
        }

        return $billings->filter(function (Billing $billing) use ($filters): bool {
            return $this->scheduleState($billing) === $filters['schedule_state'];
        });
    }

    protected function buildSummary(Collection $billings, Workspace $workspace): array
    {
        $monthlyRunRate = (float) $billings->sum(fn (Billing $billing): float => $this->monthlyEquivalent($billing));
        $collectedTotal = (float) $billings->sum(fn (Billing $billing): float => $this->collectedTotal($billing));

        return [
            'total_billings' => $billings->count(),
            'active_billings' => $billings->where('status', 'active')->count(),
            'paused_billings' => $billings->where('status', 'paused')->count(),
            'completed_billings' => $billings->where('status', 'completed')->count(),
            'due_this_month_billings' => $billings->filter(fn (Billing $billing): bool => $billing->next_invoice_date !== null && $billing->status === 'active' && $billing->next_invoice_date->betweenIncluded(now()->startOfDay(), now()->endOfMonth()))->count(),
            'overdue_billings' => $billings->filter(fn (Billing $billing): bool => $this->scheduleState($billing) === 'overdue')->count(),
            'invoices_generated' => $billings->sum(fn (Billing $billing): int => $billing->invoices->count()),
            'monthly_run_rate_total' => $monthlyRunRate,
            'monthly_run_rate_total_label' => $this->currency($monthlyRunRate, $workspace),
            'collected_total' => $collectedTotal,
            'collected_total_label' => $this->currency($collectedTotal, $workspace),
            'upcoming_invoice_label' => $billings
                ->filter(fn (Billing $billing): bool => $billing->next_invoice_date !== null)
                ->sortBy('next_invoice_date')
                ->first()?->next_invoice_date?->format('d M Y') ?? 'No invoice date',
        ];
    }

    protected function filterOptions(Workspace $workspace): array
    {
        return [
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
            'types' => [
                ['value' => 'retainer', 'label' => 'Retainer'],
                ['value' => 'project_based', 'label' => 'Project Based'],
            ],
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'paused', 'label' => 'Paused'],
                ['value' => 'completed', 'label' => 'Completed'],
            ],
            'billingCycles' => [
                ['value' => 'monthly', 'label' => 'Monthly'],
                ['value' => 'quarterly', 'label' => 'Quarterly'],
                ['value' => 'yearly', 'label' => 'Yearly'],
            ],
            'scheduleStates' => [
                ['value' => 'due_now', 'label' => 'Due Now'],
                ['value' => 'upcoming', 'label' => 'Upcoming'],
                ['value' => 'overdue', 'label' => 'Overdue'],
                ['value' => 'paused', 'label' => 'Paused'],
            ],
        ];
    }

    protected function transformBilling(Billing $billing, Workspace $workspace): array
    {
        $scheduleState = $this->scheduleState($billing);
        $paymentHistory = $this->paymentHistory($billing, $workspace);

        return [
            'id' => $billing->getKey(),
            'client_id' => $billing->client_id,
            'project_id' => $billing->project_id,
            'name' => $billing->name,
            'type' => $billing->type,
            'type_label' => $billing->type === 'project_based' ? 'Project Based' : 'Retainer',
            'amount' => (float) $billing->amount,
            'amount_label' => $this->currency((float) $billing->amount, $workspace),
            'billing_cycle' => $billing->billing_cycle,
            'billing_cycle_label' => ucfirst($billing->billing_cycle ?? 'monthly'),
            'start_date' => $billing->start_date?->toDateString(),
            'start_date_label' => $billing->start_date?->format('d M Y'),
            'next_invoice_date' => $billing->next_invoice_date?->toDateString(),
            'next_invoice_date_label' => $billing->next_invoice_date?->format('d M Y'),
            'status' => $billing->status,
            'status_label' => ucfirst($billing->status ?? 'active'),
            'schedule_state' => $scheduleState,
            'schedule_state_label' => $this->scheduleStateLabel($scheduleState),
            'monthly_equivalent_label' => $this->currency($this->monthlyEquivalent($billing), $workspace),
            'invoices_generated_count' => $billing->invoices->count(),
            'collected_total_label' => $this->currency($this->collectedTotal($billing), $workspace),
            'client' => $billing->client ? [
                'id' => $billing->client->getKey(),
                'name' => $billing->client->company_name,
                'pic_name' => $billing->client->pic_name,
                'email' => $billing->client->email,
                'phone' => $billing->client->phone,
            ] : null,
            'project' => $billing->project ? [
                'id' => $billing->project->getKey(),
                'name' => $billing->project->name,
            ] : null,
            'recent_invoices' => $this->recentInvoices($billing, $workspace),
            'payment_history' => $paymentHistory,
            'notes' => $billing->notes,
        ];
    }

    protected function recentInvoices(Billing $billing, Workspace $workspace): array
    {
        return $billing->invoices
            ->take(5)
            ->map(function (Invoice $invoice) use ($workspace): array {
                return [
                    'id' => $invoice->getKey(),
                    'number' => $invoice->number,
                    'status' => $invoice->status,
                    'status_label' => ucfirst($invoice->status),
                    'due_date_label' => $invoice->due_date?->format('d M Y'),
                    'total_label' => $this->currency((float) $invoice->total, $workspace),
                    'paid_amount_label' => $this->currency((float) $invoice->paid_amount, $workspace),
                    'payment_count' => $invoice->payments->count(),
                ];
            })
            ->values()
            ->all();
    }

    protected function paymentHistory(Billing $billing, Workspace $workspace): array
    {
        return $billing->invoices
            ->flatMap(function (Invoice $invoice) use ($workspace): Collection {
                return $invoice->payments->map(function (Payment $payment) use ($invoice, $workspace): array {
                    return [
                        'id' => $payment->getKey(),
                        'invoice_number' => $invoice->number,
                        'amount_label' => $this->currency((float) $payment->amount, $workspace),
                        'method_label' => ucfirst(str_replace('_', ' ', $payment->method)),
                        'status_label' => ucfirst($payment->status),
                        'paid_at_label' => $payment->paid_at?->format('d M Y'),
                        'verifier_name' => $payment->verifier?->name,
                        'sort_key' => $payment->paid_at?->timestamp ?? 0,
                    ];
                });
            })
            ->sortByDesc('sort_key')
            ->take(6)
            ->values()
            ->map(fn (array $payment): array => array_diff_key($payment, ['sort_key' => true]))
            ->all();
    }

    protected function scheduleState(Billing $billing): string
    {
        if ($billing->status === 'paused') {
            return 'paused';
        }

        if ($billing->status === 'completed') {
            return 'completed';
        }

        if ($billing->next_invoice_date === null) {
            return 'upcoming';
        }

        if ($billing->next_invoice_date->isPast() && ! $billing->next_invoice_date->isToday()) {
            return 'overdue';
        }

        if ($billing->next_invoice_date->isToday()) {
            return 'due_now';
        }

        return 'upcoming';
    }

    protected function scheduleStateLabel(string $state): string
    {
        return match ($state) {
            'due_now' => 'Due Today',
            'upcoming' => 'Upcoming',
            'overdue' => 'Overdue',
            'paused' => 'Paused',
            'completed' => 'Completed',
            default => 'Upcoming',
        };
    }

    protected function monthlyEquivalent(Billing $billing): float
    {
        $amount = (float) $billing->amount;

        return match ($billing->billing_cycle) {
            'quarterly' => round($amount / 3, 2),
            'yearly' => round($amount / 12, 2),
            default => $amount,
        };
    }

    protected function collectedTotal(Billing $billing): float
    {
        return (float) $billing->invoices->sum(fn (Invoice $invoice): float => (float) $invoice->payments->sum('amount'));
    }

    protected function currency(float $amount, Workspace $workspace): string
    {
        return number_format($amount, 0, ',', '.') . ' ' . ($workspace->currency ?? 'IDR');
    }
}
