<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StripeWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('products')->group(function (): void {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{product}', [ProductController::class, 'show']);
});

Route::prefix('templates')->group(function (): void {
    Route::get('/', [TemplateController::class, 'index']);
    Route::post('/generate-ai', [TemplateController::class, 'generateFromAi']);
    Route::get('/{template}', [TemplateController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::prefix('projects')->group(function (): void {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::get('/{project}', [ProjectController::class, 'show']);
        Route::put('/{project}', [ProjectController::class, 'update']);
        Route::post('/{project}/generate-from-template', [ProjectController::class, 'generateFromTemplate']);
        Route::post('/{project}/ai-customize', [ProjectController::class, 'aiCustomize']);
    });

    Route::prefix('orders')->group(function (): void {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{order}', [OrderController::class, 'show']);
    });
});

Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
