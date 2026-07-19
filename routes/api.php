<?php

use App\Http\Controllers\Api\InternalIntegrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('internal/v1')
    ->middleware('throttle:60,1')
    ->group(function (): void {
        Route::get('/health', [InternalIntegrationController::class, 'health'])
            ->middleware('integration.client:integration:health');

        Route::post('/events', [InternalIntegrationController::class, 'events'])
            ->middleware('integration.client:events:push');
    });
