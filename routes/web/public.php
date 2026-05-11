<?php

use App\Http\Controllers\App\Project\ContractPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/contracts/esign/{token}', [ContractPublicController::class, 'showEsign'])->name('contracts.public.esign');
Route::post('/contracts/esign/{token}', [ContractPublicController::class, 'sign'])->name('contracts.public.sign');
