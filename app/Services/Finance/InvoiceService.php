<?php

namespace App\Services\Finance;

use App\Integrations\Pakasir\PakasirService;
use App\Jobs\Finance\GenerateInvoicePdf;
use App\Models\ActivityFeed;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Transaction;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function create(Workspace $workspace, array $data): Invoice
    {
        return DB::transaction(function () use ($workspace, $data): Invoice {
            [$client, $project, $quotation, $contract] = $this->resolveRelations($workspace, $data);
            $type = $data['type'] ?? 'invoice';

            $invoice = Invoice::query()->create([
                'workspace_id' => $workspace->getKey(),
                'client_id' => $client?->getKey(),
                'project_id' => $project?->getKey(),
                'quotation_id' => $quotation?->getKey(),
                'contract_id' => $contract?->getKey(),
                'billing_id' => $data['billing_id'] ?? null,
                'number' => $this->generateInvoiceNumber($workspace, $type),
                'type' => $type,
                'status' => $data['status'] ?? 'draft',
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_rate' => $data['tax_rate'] ?? 11,
                'paid_amount' => 0,
                'currency' => strtoupper($data['currency'] ?? $workspace->currency ?? 'IDR'),
                'due_date' => $data['due_date'] ?? now()->addDays(14)->toDateString(),
                'is_recurring' => (bool) ($data['is_recurring'] ?? false),
                'recurrence_rule' => ! empty($data['is_recurring']) ? ($data['recurrence_rule'] ?? null) : null,
                'payment_method' => $data['payment_method'] ?? null,
                'pakasir_order_id' => $data['pakasir_order_id'] ?? null,
                'pakasir_payment_url' => $data['pakasir_payment_url'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->syncItems($invoice, $data['items'] ?? [], $quotation);
            $this->applyStatusSideEffects($invoice, $data['status'] ?? 'draft');
            $this->logActivity($workspace, $invoice, sprintf('Invoice %s berhasil dibuat.', $invoice->number), 'create', 'emerald');

            $invoice = $invoice->refresh()->load($this->detailRelations());
            $this->syncPakasirLink($invoice);
            GenerateInvoicePdf::dispatch($invoice)->afterCommit();

            return $invoice->refresh()->load($this->detailRelations());
        });
    }

    public function update(Workspace $workspace, Invoice $invoice, array $data): Invoice
    {
        abort_unless($invoice->workspace_id === $workspace->getKey(), 404);

        return DB::transaction(function () use ($workspace, $invoice, $data): Invoice {
            [$client, $project, $quotation, $contract] = $this->resolveRelations($workspace, $data, $invoice);

            $invoice->update([
                'client_id' => $client?->getKey(),
                'project_id' => $project?->getKey(),
                'quotation_id' => $quotation?->getKey(),
                'contract_id' => $contract?->getKey(),
                'billing_id' => $data['billing_id'] ?? $invoice->billing_id,
                'type' => $data['type'] ?? $invoice->type,
                'status' => $data['status'] ?? $invoice->status,
                'discount_amount' => $data['discount_amount'] ?? $invoice->discount_amount,
                'tax_rate' => $data['tax_rate'] ?? $invoice->tax_rate,
                'currency' => strtoupper($data['currency'] ?? $invoice->currency),
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'is_recurring' => (bool) ($data['is_recurring'] ?? false),
                'recurrence_rule' => ! empty($data['is_recurring']) ? ($data['recurrence_rule'] ?? null) : null,
                'payment_method' => $data['payment_method'] ?? null,
                'pakasir_order_id' => $data['pakasir_order_id'] ?? null,
                'pakasir_payment_url' => $data['pakasir_payment_url'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($invoice, $data['items'] ?? [], $quotation);
            $this->applyStatusSideEffects($invoice, $data['status'] ?? $invoice->status);
            $this->logActivity($workspace, $invoice, sprintf('Invoice %s diperbarui.', $invoice->number), 'update', 'amber');

            $invoice = $invoice->refresh()->load($this->detailRelations());
            $this->syncPakasirLink($invoice);
            GenerateInvoicePdf::dispatch($invoice)->afterCommit();

            return $invoice->refresh()->load($this->detailRelations());
        });
    }

    public function delete(Workspace $workspace, Invoice $invoice): void
    {
        abort_unless($invoice->workspace_id === $workspace->getKey(), 404);

        $number = $invoice->number;
        $invoice->delete();

        ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Invoice::class,
            'subject_id' => null,
            'description' => sprintf('Invoice %s dihapus.', $number),
            'metadata' => [
                'action' => 'delete',
                'icon' => 'Trash2',
                'color' => 'rose',
            ],
            'created_at' => now(),
        ]);
    }

    public function updateStatus(Workspace $workspace, Invoice $invoice, string $status): Invoice
    {
        abort_unless($invoice->workspace_id === $workspace->getKey(), 404);

        return DB::transaction(function () use ($workspace, $invoice, $status): Invoice {
            $this->applyStatusSideEffects($invoice, $status);
            $this->logActivity($workspace, $invoice, sprintf('Status invoice %s diubah ke %s.', $invoice->number, $status), 'status', 'sky');
            GenerateInvoicePdf::dispatch($invoice)->afterCommit();

            return $invoice->refresh()->load($this->detailRelations());
        });
    }

    public function approve(Workspace $workspace, Invoice $invoice): Invoice
    {
        abort_unless($invoice->workspace_id === $workspace->getKey(), 404);

        $invoice->update([
            'internal_approved_at' => now(),
            'internal_approved_by' => Auth::id(),
        ]);

        $this->logActivity($workspace, $invoice, sprintf('Invoice %s disetujui internal.', $invoice->number), 'approve', 'emerald');

        return $invoice->refresh()->load($this->detailRelations());
    }

    public function recordPayment(Workspace $workspace, Invoice $invoice, array $data): Invoice
    {
        abort_unless($invoice->workspace_id === $workspace->getKey(), 404);
        abort_if($invoice->type === 'credit_note', 422, 'Credit note cannot receive incoming payment.');

        return DB::transaction(function () use ($workspace, $invoice, $data): Invoice {
            $paidAt = $data['paid_at'] ?? now()->toDateString();

            Payment::query()->create([
                'workspace_id' => $workspace->getKey(),
                'invoice_id' => $invoice->getKey(),
                'amount' => $data['amount'],
                'method' => $data['method'],
                'status' => 'verified',
                'notes' => $data['notes'] ?? null,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'paid_at' => $paidAt,
            ]);

            Transaction::query()->create([
                'workspace_id' => $workspace->getKey(),
                'invoice_id' => $invoice->getKey(),
                'project_id' => $invoice->project_id,
                'type' => 'income',
                'category' => 'invoice_payment',
                'amount' => $data['amount'],
                'description' => sprintf('Manual payment confirmation for invoice %s', $invoice->number),
                'date' => $paidAt,
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            $newPaidAmount = min((float) $invoice->total, (float) $invoice->paid_amount + (float) $data['amount']);
            $status = $newPaidAmount >= (float) $invoice->total ? 'paid' : 'partial';

            $invoice->update([
                'payment_method' => $data['method'],
                'paid_amount' => $newPaidAmount,
                'status' => $status,
                'paid_at' => $status === 'paid' ? now() : null,
            ]);

            $this->logActivity($workspace, $invoice, sprintf('Pembayaran untuk invoice %s dikonfirmasi.', $invoice->number), 'payment', 'emerald');
            GenerateInvoicePdf::dispatch($invoice)->afterCommit();

            return $invoice->refresh()->load($this->detailRelations());
        });
    }

    protected function resolveRelations(Workspace $workspace, array $data, ?Invoice $invoice = null): array
    {
        $project = null;
        $quotation = null;
        $contract = null;

        if (! empty($data['project_id'])) {
            $project = Project::query()
                ->where('workspace_id', $workspace->getKey())
                ->findOrFail($data['project_id']);
        } elseif ($invoice?->project_id) {
            $project = Project::query()
                ->where('workspace_id', $workspace->getKey())
                ->find($invoice->project_id);
        }

        if (! empty($data['quotation_id'])) {
            $quotation = Quotation::query()
                ->where('workspace_id', $workspace->getKey())
                ->with('items')
                ->findOrFail($data['quotation_id']);

            abort_if($quotation->status !== 'approved', 422, 'Quotation must be approved before creating invoice.');
        } elseif ($invoice?->quotation_id) {
            $quotation = Quotation::query()
                ->where('workspace_id', $workspace->getKey())
                ->with('items')
                ->find($invoice->quotation_id);
        }

        if (! empty($data['contract_id'])) {
            $contract = Contract::query()
                ->where('workspace_id', $workspace->getKey())
                ->findOrFail($data['contract_id']);

            abort_if($contract->status !== 'signed', 422, 'Contract must be signed before creating invoice.');
        } elseif ($invoice?->contract_id) {
            $contract = Contract::query()
                ->where('workspace_id', $workspace->getKey())
                ->find($invoice->contract_id);
        }

        $clientId = $data['client_id']
            ?? $project?->client_id
            ?? $contract?->client_id
            ?? $quotation?->client_id
            ?? $invoice?->client_id;

        $resolvedProject = $project;

        if ($resolvedProject === null && $contract?->project_id) {
            $resolvedProject = Project::query()
                ->where('workspace_id', $workspace->getKey())
                ->find($contract->project_id);
        }

        $client = null;

        if ($clientId) {
            $client = Client::query()
                ->where('workspace_id', $workspace->getKey())
                ->findOrFail($clientId);
        }

        return [$client, $resolvedProject, $quotation, $contract];
    }

    protected function syncItems(Invoice $invoice, array $items, ?Quotation $quotation = null): void
    {
        $sourceItems = collect($items);

        if ($sourceItems->isEmpty() && $quotation !== null) {
            $sourceItems = $quotation->items->map(fn ($item): array => [
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ]);
        }

        abort_if($sourceItems->isEmpty(), 422, 'Invoice items are required.');

        $invoice->items()->delete();

        foreach ($sourceItems->values() as $index => $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);

            $invoice->items()->create([
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => max(0, $quantity * $unitPrice),
                'order_index' => $index,
            ]);
        }

        $invoice->recalculateTotals();
    }

    protected function applyStatusSideEffects(Invoice $invoice, string $status): void
    {
        if ($status === 'sent' && $invoice->internal_approved_at === null) {
            abort(422, 'Invoice needs internal approval before sending.');
        }

        $attributes = ['status' => $status];

        if ($status === 'sent') {
            $attributes['sent_at'] = $invoice->sent_at ?? now();
        }

        if ($status === 'paid') {
            $attributes['paid_amount'] = (float) $invoice->total;
            $attributes['paid_at'] = $invoice->paid_at ?? now();
        }

        if ($status === 'partial') {
            $attributes['paid_at'] = null;
        }

        if (in_array($status, ['draft', 'overdue'], true)) {
            $attributes['paid_at'] = $status === 'draft' ? null : $invoice->paid_at;
        }

        $invoice->update($attributes);
    }

    protected function generateInvoiceNumber(Workspace $workspace, string $type): string
    {
        $prefix = match ($type) {
            'proforma' => 'PFR',
            'credit_note' => 'CRN',
            default => 'INV',
        } . '-' . now()->format('Ym');

        $count = Invoice::query()
            ->where('workspace_id', $workspace->getKey())
            ->where('number', 'like', "{$prefix}-%")
            ->count() + 1;

        return sprintf('%s-%04d', $prefix, $count);
    }

    protected function detailRelations(): array
    {
        return [
            'client:id,company_name,pic_name',
            'project:id,name',
            'quotation:id,number,title,status',
            'contract:id,title,status',
            'billing:id,name,client_id,project_id',
            'creator:id,name',
            'internalApprover:id,name',
            'items',
            'payments.verifier:id,name',
        ];
    }

    protected function syncPakasirLink(Invoice $invoice): void
    {
        if (! is_string($invoice->payment_method) || ! str_starts_with($invoice->payment_method, 'pakasir_')) {
            return;
        }

        if (filled($invoice->pakasir_payment_url) && filled($invoice->pakasir_order_id)) {
            return;
        }

        $pakasir = app(PakasirService::class);

        if (! $pakasir->isConfigured()) {
            return;
        }

        $result = $pakasir->createPaymentLink($invoice);

        if (! ($result['success'] ?? false)) {
            Log::warning('Pakasir link generation failed.', [
                'invoice_id' => $invoice->getKey(),
                'invoice_number' => $invoice->number,
                'message' => $result['message'] ?? 'Unknown error',
            ]);

            return;
        }

        $invoice->updateQuietly([
            'pakasir_order_id' => $result['order_id'] ?? null,
            'pakasir_payment_url' => $result['payment_url'] ?? null,
        ]);
    }

    protected function logActivity(
        Workspace $workspace,
        Invoice $invoice,
        string $description,
        string $action,
        string $color
    ): ActivityFeed {
        return ActivityFeed::query()->create([
            'workspace_id' => $workspace->getKey(),
            'user_id' => Auth::id(),
            'type' => 'finance',
            'subject_type' => Invoice::class,
            'subject_id' => $invoice->getKey(),
            'description' => $description,
            'metadata' => [
                'action' => $action,
                'icon' => match ($action) {
                    'create' => 'FilePlus2',
                    'update' => 'Pencil',
                    'approve' => 'BadgeCheck',
                    'status' => 'Clock3',
                    'payment' => 'CircleDollarSign',
                    default => 'ReceiptText',
                },
                'color' => $color,
            ],
            'created_at' => now(),
        ]);
    }
}
