<?php

use App\Http\Controllers\Api\V1\AlertController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MachineController;
use App\Http\Controllers\Api\V1\MetricController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Middleware\AuthenticateTenantApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::post('metrics', [MetricController::class, 'store'])
        ->middleware([AuthenticateTenantApiKey::class, 'throttle:metrics']);

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::get('machines', [MachineController::class, 'index']);
        Route::post('machines', [MachineController::class, 'store']);
        Route::get('machines/{machine}', [MachineController::class, 'show']);
        Route::put('machines/{machine}', [MachineController::class, 'update']);
        Route::delete('machines/{machine}', [MachineController::class, 'destroy']);
        Route::get('machines/{machine}/metrics', [MachineController::class, 'metrics']);
        Route::get('machines/{machine}/stats', [MachineController::class, 'stats']);

        Route::get('alerts', [AlertController::class, 'index']);
        Route::post('alerts/{alert}/seen', [AlertController::class, 'seen']);
        Route::post('alerts/{alert}/resolve', [AlertController::class, 'resolve']);

        Route::get('reports/summary', [ReportController::class, 'summary']);
    });
});
