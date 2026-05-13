<?php

namespace App\Services\Finance;

use App\Models\ActivityFeed;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {
    }

    public function createBilling(Workspace $workspace, array $data): Billing
    {
        $billing = Billing::query()->create([
            'workspace_id' => $workspace->getKey(),
            'client_id' => $this->resolveClient($workspace, $data['client_id'])->getKey(),
            'project_id' => $this->resolveProject($workspace, $data['project_id'] ?? null)?->getKey(),
            'name' => $data['name'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'billing_cycle' => $data['billing_cycle'],
            'start_date' => $data['start_date'],
            'next_invoice_date' => $data['next_invoice_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logActivity($workspace, $billing, sprintf('Billing %s berhasil dibuat.', $billing->name), 'create', 'emerald');

        return $billing->refresh()->load(['client:id,company_name,pic_name', 'project:id,name', 'invoices.payments']);
    }

    public function updateBilling(Workspace $workspace, Billing $billing, array $data): Billing
    {
        abort_unless($billing->workspace_id === $workspace->getKey(), 404);

        $billing->update([
            'client_id' => $this->resolveClient($workspace, $data['client_id'])->getKey(),
            'project_id' => $this->resolveProject($workspace, $data['project_id'] ?? null)?->getKey(),
            'name' => $data['name'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'billing_cycle' => $data['billing_cycle'],
            'start_date' => $data['start_date'],
            'next_invoice_date' => $data['next_invoice_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logActivity($workspace, $billing, sprintf('Billing %s diperbarui.', $billing->name), 'update', 'amber');

        return $billing->refresh()->load(['client:id,company_name,pic_name', 'project:id,name', 'invoices.payments']);
    }

    public function deleteBilling(Workspace $workspace, Billing $billing): void
    {
        abort_unless($billing->workspace_id === $workspace->getKey(), 404);

        $name = $billing->name;
        $billing->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Billing::class,
            'subject_id' => null,
            'description' => sprintf('Billing %s dihapus.', $name),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    public function generateInvoice(Workspace $workspace, Billing $billing): Invoice
    {
        abort_unless($billing->workspace_id === $workspace->getKey(), 404);
        abort_if(in_array($billing->status, ['paused', 'completed'], true), 422, 'Billing is not active.');

        return DB::transaction(function () use ($workspace, $billing): Invoice {
            $invoice = $this->invoiceService->create($workspace, [
                'billing_id' => $billing->getKey(),
                'client_id' => $billing->client_id,
                'project_id' => $billing->project_id,
                'type' => 'invoice',
                'status' => 'draft',
                'currency' => $workspace->currency ?? 'IDR',
                'due_date' => now()->addDays(14)->toDateString(),
                'discount_amount' => 0,
                'tax_rate' => 11,
                'is_recurring' => true,
                'recurrence_rule' => $billing->billing_cycle,
                'notes' => sprintf('Auto-generated from billing schedule %s.', $billing->name),
                'items' => [[
                    'name' => $billing->name,
                    'description' => $billing->notes ?: sprintf('%s billing schedule', ucfirst($billing->type)),
                    'quantity' => 1,
                    'unit_price' => $billing->amount,
                ]],
            ]);

            $billing->update([
                'next_invoice_date' => $this->advanceNextInvoiceDate($billing),
            ]);

            $this->logActivity($workspace, $billing, sprintf('Invoice %s digenerate dari billing %s.', $invoice->number, $billing->name), 'generate', 'sky');

            return $invoice;
        });
    }

    public function generateDueInvoices(Workspace $workspace): int
    {
        $billings = Billing::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('status', 'active')
            ->whereDate('next_invoice_date', '<=', now()->toDateString())
            ->orderBy('next_invoice_date')
            ->get();

        $count = 0;

        foreach ($billings as $billing) {
            $this->generateInvoice($workspace, $billing);
            $count++;
        }

        return $count;
    }

    protected function resolveClient(Workspace $workspace, string $clientId): Client
    {
        return Client::query()
            ->where('workspace_id', $workspace->getKey())
            ->findOrFail($clientId);
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

    protected function advanceNextInvoiceDate(Billing $billing): string
    {
        $nextDate = $billing->next_invoice_date?->copy() ?? now()->startOfDay();

        do {
            $nextDate = match ($billing->billing_cycle) {
                'quarterly' => $nextDate->addMonthsNoOverflow(3),
                'yearly' => $nextDate->addYear(),
                default => $nextDate->addMonthNoOverflow(),
            };
        } while ($nextDate->lte(now()->startOfDay()));

        return $nextDate->toDateString();
    }

    protected function logActivity(
        Workspace $workspace,
        Billing $billing,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Billing::class,
            'subject_id' => $billing->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'FilePlus2',
                    'update' => 'Pencil',
                    'generate' => 'RefreshCw',
                    default => 'ReceiptText',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }
}
