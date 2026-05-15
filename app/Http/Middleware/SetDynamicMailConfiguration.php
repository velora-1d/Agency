<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetDynamicMailConfiguration
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $workspace = $request->route('workspace');

        if ($workspace instanceof \App\Models\Workspace) {
            // Jika workspace memiliki pengaturan SMTP sendiri, gunakan itu
            if ($workspace->smtp_host && $workspace->smtp_username && $workspace->smtp_password) {
                Config::set('mail.mailers.smtp.host', $workspace->smtp_host);
                Config::set('mail.mailers.smtp.port', $workspace->smtp_port ?? 587);
                Config::set('mail.mailers.smtp.username', $workspace->smtp_username);
                Config::set('mail.mailers.smtp.password', $workspace->smtp_password);
                
                // Gunakan nama workspace sebagai nama pengirim
                Config::set('mail.from.name', $workspace->name);
                
                // Jika ada email khusus pengirim di settings, gunakan itu. 
                // Jika tidak, biarkan default dari .env (karena Resend butuh domain terverifikasi)
                $fromAddress = data_get($workspace->settings, 'mail_from_address');
                if ($fromAddress) {
                    Config::set('mail.from.address', $fromAddress);
                }
            }
        }

        return $next($request);
    }
}
