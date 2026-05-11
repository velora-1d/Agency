<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PakasirWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$orderId || $status !== 'PAID') {
            return response()->json(['status' => 'ignored']);
        }

        $invoice = Invoice::where('pakasir_order_id', $orderId)->first();
        if (!$invoice) {
            Log::warning("Pakasir Webhook: Invoice not found for order {$orderId}");
            return response()->json(['status' => 'not_found']);
        }

        DB::transaction(function () use ($invoice, $payload) {
            // 1. Update Invoice
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_amount' => $invoice->total,
            ]);

            // 2. Create Payment Record
            Payment::create([
                'workspace_id' => $invoice->workspace_id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total,
                'method' => 'pakasir_' . ($payload['payment_type'] ?? 'unknown'),
                'status' => 'verified',
                'pakasir_transaction_id' => $payload['transaction_id'] ?? null,
                'paid_at' => now(),
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
                'date' => now(),
            ]);
        });

        return response()->json(['status' => 'success']);
    }
}
