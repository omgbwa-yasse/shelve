<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AiModelController;
use App\Http\Controllers\AiInteractionController;
use App\Http\Controllers\AiJobController;
use App\Http\Controllers\OllamaController;

    // routes/api.php - Ajouter ces routes
    Route::prefix('ai/ollama')->name('api.ai.ollama.')->group(function () {
        // Santé et modèles
        Route::get('health', [AiModelController::class, 'healthCheck'])->name('health');
        Route::get('models', [AiModelController::class, 'getOllamaModels'])->name('models');
        Route::post('models/sync', [AiModelController::class, 'syncOllamaModels'])->name('models.sync');

        // Interactions
        Route::post('interact', [AiInteractionController::class, 'createAndProcess'])->name('interact');
        Route::post('chat', [AiInteractionController::class, 'chat'])->name('chat');
        Route::get('stream', [AiInteractionController::class, 'stream'])->name('stream');

        // Jobs par lot
        Route::post('batch', [AiJobController::class, 'createBatch'])->name('batch.create');
        Route::get('batch/{job}', [AiJobController::class, 'getJobStatus'])->name('batch.status');

        // Test et dashboard
        Route::post('test', [OllamaController::class, 'test'])->name('test');
        Route::get('dashboard', [OllamaController::class, 'dashboard'])->name('dashboard');
    });