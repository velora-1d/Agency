<?php

namespace App\Services\Communication;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.evolution.url', env('EVOLUTION_API_URL'));
        $this->apiKey = config('services.evolution.key', env('EVOLUTION_API_KEY'));
    }

    public function sendMessage(string $instance, string $number, string $text): bool
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
        ])->post("{$this->baseUrl}/message/sendText/{$instance}", [
            'number' => $this->normalizeNumber($number),
            'options' => [
                'delay' => 1200,
                'presence' => 'composing',
                'linkPreview' => false,
            ],
            'textMessage' => [
                'text' => $text,
            ],
        ]);

        if ($response->failed()) {
            Log::error("Evolution API Send Message Failed: " . $response->body());
            return false;
        }

        return true;
    }

    public function checkStatus(string $instance): string
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
        ])->get("{$this->baseUrl}/instance/connectionState/{$instance}");

        if ($response->failed()) {
            return 'error';
        }

        return $response->json('instance.state') ?? 'disconnected';
    }

    protected function normalizeNumber(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }
}
