<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ninerouter.url', env('NINE_ROUTER_BASE_URL', 'http://localhost:20128/v1'));
        $this->apiKey = config('services.ninerouter.key', env('NINE_ROUTER_API_KEY'));
    }

    /**
     * Kirim prompt ke AI via 9Router
     * 
     * @param string $prompt Pesan dari user
     * @param string $model Model dengan prefix (cc/claude-3-5-sonnet, oa/gpt-4o, ge/gemini-1.5-pro)
     * @param string $system System prompt (optional)
     */
    public function ask(string $prompt, string $model = 'cc/claude-3-5-sonnet', string $system = 'Kamu adalah asisten profesional untuk sistem Kantor Digital.'): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

            if ($response->failed()) {
                Log::error("9Router AI Error: " . $response->body());
                return null;
            }

            return $response->json('choices.0.message.content');

        } catch (\Exception $e) {
            Log::error("9Router Connection Exception: " . $e->getMessage());
            return null;
        }
    }
}
