<?php

namespace App\Integrations\Pakasir;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PakasirService
{
    protected string $baseUrl;
    protected string $publicBaseUrl;
    protected ?string $apiKey;
    protected ?string $project;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.pakasir.base_url', 'https://app.pakasir.com'), '/');
        $this->publicBaseUrl = $this->resolvePublicBaseUrl($this->baseUrl);
        $this->apiKey = config('services.pakasir.api_key');
        $this->project = config('services.pakasir.project');
    }

    /**
     * Membuat Link Pembayaran (Payment Link / Invoice) di Pakasir.
     */
    public function isConfigured(): bool
    {
        return filled($this->baseUrl);
    }

    public function canVerifyTransactions(): bool
    {
        return filled($this->apiKey);
    }

    public function createPaymentLink(Invoice $invoice): array
    {
        $project = $this->resolveProject($invoice->workspace?->slug);

        if (! $this->isConfigured() || blank($project)) {
            return [
                'success' => false,
                'message' => 'Konfigurasi proyek Pakasir belum diisi.',
            ];
        }

        $amount = (int) round((float) $invoice->total);
        $query = [
            'order_id' => $invoice->number,
            'redirect' => route('workspace.finance.invoices.index', $invoice->workspace->slug),
        ];

        if ($invoice->payment_method === 'pakasir_qris') {
            $query['qris_only'] = 1;
        }

        return [
            'success' => true,
            'order_id' => $invoice->number,
            'payment_url' => "{$this->publicBaseUrl}/pay/{$project}/{$amount}?" . http_build_query($query),
        ];
    }

    public function fetchTransactionDetail(string $project, int $amount, string $orderId): array
    {
        if (! $this->canVerifyTransactions()) {
            return [
                'success' => false,
                'message' => 'API key Pakasir belum diisi.',
            ];
        }

        try {
            $response = Http::acceptJson()->get("{$this->publicBaseUrl}/api/transactiondetail", [
                'project' => $project,
                'amount' => $amount,
                'order_id' => $orderId,
                'api_key' => $this->apiKey,
            ]);

            if (! $response->successful()) {
                Log::warning('Pakasir transaction detail request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'project' => $project,
                    'order_id' => $orderId,
                ]);

                return [
                    'success' => false,
                    'message' => 'Gagal mengambil detail transaksi Pakasir.',
                ];
            }

            return [
                'success' => true,
                'transaction' => $response->json('transaction') ?? [],
            ];
        } catch (\Throwable $exception) {
            Log::warning('Pakasir transaction detail exception.', [
                'message' => $exception->getMessage(),
                'project' => $project,
                'order_id' => $orderId,
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memverifikasi transaksi Pakasir.',
            ];
        }
    }

    protected function resolveProject(?string $fallback = null): ?string
    {
        return filled($this->project) ? $this->project : $fallback;
    }

    protected function resolvePublicBaseUrl(string $baseUrl): string
    {
        if (str_contains($baseUrl, 'api.pakasir.com')) {
            return 'https://app.pakasir.com';
        }

        if (str_contains($baseUrl, '/v1')) {
            return preg_replace('#/v1/?$#', '', $baseUrl) ?: $baseUrl;
        }

        return $baseUrl;
    }
}
