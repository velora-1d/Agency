<?php

namespace App\Jobs\Finance;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateInvoicePdf implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Invoice $invoice)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->invoice->load([
            'workspace',
            'client',
            'items',
        ]);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'workspace' => $this->invoice->workspace,
        ]);

        $filename = sprintf('invoice-%s-%s.pdf', $this->invoice->number, now()->format('YmdHis'));
        $path = sprintf('workspaces/%s/invoices/%s', $this->invoice->workspace_id, $filename);

        Storage::disk('public')->put($path, $pdf->output());

        $this->invoice->updateQuietly([
            'pdf_path' => $path,
        ]);
    }
}
