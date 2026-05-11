<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): RedirectResponse {
    if (auth()->check()) {
        return redirect()->route('app.home');
    }

    return redirect()->route('login');
});

require __DIR__.'/web/auth.php';
require __DIR__.'/web/app.php';
require __DIR__.'/web/client-portal.php';
require __DIR__.'/web/webhooks.php';
require __DIR__.'/web/public.php';
