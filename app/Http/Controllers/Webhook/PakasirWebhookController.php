<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Integrations\Pakasir\PakasirService;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PakasirWebhookController extends Controller
{
    public function handle(Request $request, PakasirService $pakasir)
    {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? null;
        $status = strtolower((string) ($payload['status'] ?? ''));
        $completedStatus = in_array($status, ['paid', 'completed'], true);
        $amount = (int) ($payload['amount'] ?? 0);
        $project = (string) ($payload['project'] ?? '');

        if (!$orderId || !$completedStatus) {
            return response()->json(['status' => 'ignored']);
        }

        $invoice = Invoice::where('pakasir_order_id', $orderId)->first();
        if (!$invoice) {
            Log::warning("Pakasir Webhook: Invoice not found for order {$orderId}");
            return response()->json(['status' => 'not_found']);
        }

        $expectedProject = config('services.pakasir.project', $invoice->workspace?->slug);
        $expectedAmount = (int) round((float) $invoice->total);

        if ($amount !== $expectedAmount || ($project !== '' && $expectedProject !== null && $project !== $expectedProject)) {
            Log::warning('Pakasir Webhook: Payload mismatch.', [
                'order_id' => $orderId,
                'payload_amount' => $amount,
                'expected_amount' => $expectedAmount,
                'payload_project' => $project,
                'expected_project' => $expectedProject,
            ]);

            return response()->json(['status' => 'invalid'], 422);
        }

        if ($pakasir->canVerifyTransactions()) {
            $detail = $pakasir->fetchTransactionDetail(
                (string) $expectedProject,
                $expectedAmount,
                (string) $orderId,
            );

            if (! ($detail['success'] ?? false)) {
                return response()->json(['status' => 'verification_failed'], 502);
            }

            $transaction = $detail['transaction'] ?? [];
            $detailStatus = strtolower((string) ($transaction['status'] ?? ''));
            $detailAmount = (int) ($transaction['amount'] ?? 0);
            $detailOrderId = (string) ($transaction['order_id'] ?? '');
            $detailProject = (string) ($transaction['project'] ?? '');

            if (
                ! in_array($detailStatus, ['paid', 'completed'], true)
                || $detailAmount !== $expectedAmount
                || $detailOrderId !== $orderId
                || ($detailProject !== '' && $detailProject !== (string) $expectedProject)
            ) {
                Log::warning('Pakasir Webhook: Transaction detail mismatch.', [
                    'order_id' => $orderId,
                    'detail' => $transaction,
                    'expected_project' => $expectedProject,
                    'expected_amount' => $expectedAmount,
                ]);

                return response()->json(['status' => 'verification_mismatch'], 422);
            }
        }

        DB::transaction(function () use ($invoice, $payload) {
            $rawMethod = $payload['payment_method'] ?? $payload['payment_type'] ?? 'unknown';
            $normalizedMethod = 'pakasir_' . str($rawMethod)->lower()->replace(' ', '_')->replace('-', '_')->toString();
            $completedAt = $payload['completed_at'] ?? now();

            // 1. Update Invoice
            $invoice->update([
                'status' => 'paid',
                'paid_at' => $completedAt,
                'paid_amount' => $invoice->total,
                'payment_method' => $normalizedMethod,
            ]);

            // 2. Create Payment Record
            Payment::create([
                'workspace_id' => $invoice->workspace_id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total,
                'method' => $normalizedMethod,
                'status' => 'verified',
                'pakasir_transaction_id' => $payload['transaction_id'] ?? null,
                'paid_at' => $completedAt,
            ]);

            // 3. Create Transaction Entry
            Transaction::create([
                'workspace_id' => $invoice->workspace_id,
                'invoice_id' => $invoice->id,
                'project_id' => $invoice->project_id,
                'type' => 'income',
                'category' => 'development',
                'amount' => $invoice->total,
                'description' => "Automatic Payment via Pakasir for Invoice #{$invoice->number}",
                'date' => $completedAt,
            ]);
        });

        return response()->json(['status' => 'success']);
    }
}
