<?php

use App\Http\Controllers\App\Project\ContractPublicController;
use App\Http\Controllers\App\Project\FilePublicController;
use App\Http\Controllers\App\Finance\QuotationPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/contracts/esign/{token}', [ContractPublicController::class, 'showEsign'])->name('contracts.public.esign');
Route::post('/contracts/esign/{token}', [ContractPublicController::class, 'sign'])->name('contracts.public.sign');
Route::get('/files/share/{token}', [FilePublicController::class, 'showShared'])->name('files.public.show');
Route::get('/quotations/approve/{token}', [QuotationPublicController::class, 'show'])->name('quotations.public.show');
Route::post('/quotations/approve/{token}', [QuotationPublicController::class, 'decide'])->name('quotations.public.decide');
