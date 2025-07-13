<?php

use Illuminate\Support\Facades\Route;
use Mannaf\Laravel2FA\Controllers\TwoFactorController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('twofactor.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('twofactor.enable');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('twofactor.disable');
});

Route::middleware('web')->group(function () {
    Route::get('/2fa/verify', [TwoFactorController::class, 'showVerifyForm'])->name('twofactor.verify');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('twofactor.verify.post');
});
