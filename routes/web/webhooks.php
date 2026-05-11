<?php

use App\Http\Controllers\Webhook\EvolutionWebhookController;
use App\Http\Controllers\Webhook\PakasirWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')
    ->name('webhooks.')
    ->group(function (): void {
        Route::post('/evolution', [EvolutionWebhookController::class, 'handle'])->name('evolution');
        Route::post('/pakasir', [PakasirWebhookController::class, 'handle'])->name('pakasir');
    });
