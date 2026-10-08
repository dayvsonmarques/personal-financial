<?php

use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'org'])->group(function () {
    Route::get('/me', MeController::class);

    // Rotas de domínio exigem e-mail verificado (RF-02).
    Route::middleware('verified')->group(function () {
        Route::get('/organization', [OrganizationController::class, 'show']);
        Route::put('/organization', [OrganizationController::class, 'update']);
    });
});
