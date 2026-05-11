<?php

use App\Http\Controllers\Webhook\EvolutionWebhookController;
use App\Http\Controllers\Webhook\PakasirWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/evolution', [EvolutionWebhookController::class, 'handle']);
Route::post('/webhooks/pakasir', [PakasirWebhookController::class, 'handle']);
