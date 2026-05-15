<?php

use App\Http\Controllers\Webhook\EvolutionWebhookController;
use App\Http\Controllers\Webhook\PakasirWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/webhooks/evolution', [EvolutionWebhookController::class, 'handle']);
    Route::post('/webhooks/pakasir', [PakasirWebhookController::class, 'handle']);

    Route::prefix('/w/{workspace:slug}/ai-assistant')->group(function () {
        Route::post('/chat', [\App\Http\Controllers\Api\V1\System\AiAssistantController::class, 'chat'])->name('ai-assistant.chat');
    });
});
