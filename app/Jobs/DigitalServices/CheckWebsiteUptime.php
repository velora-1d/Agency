<?php

namespace App\Jobs\DigitalServices;

use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CheckWebsiteUptime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $websites = Website::where('status', '!=', 'suspended')->get();

        foreach ($websites as $website) {
            try {
                $response = Http::timeout(10)->get($website->url);
                
                $status = $response->successful() ? 'live' : 'down';
                
                $website->updateQuietly([
                    'status' => $status,
                    'last_uptime_check_at' => now(),
                ]);
            } catch (\Exception $e) {
                $website->updateQuietly([
                    'status' => 'down',
                    'last_uptime_check_at' => now(),
                ]);
            }
        }
    }
}
