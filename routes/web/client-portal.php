<?php

use App\Http\Controllers\ClientPortal\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('portal')
    ->name('client-portal.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
    });
