<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AiModelController;
use App\Http\Controllers\AiInteractionController;
use App\Http\Controllers\AiJobController;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\PublicRecordController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicUserController;
use App\Http\Controllers\PublicDocumentRequestController;
use App\Http\Controllers\PublicFeedbackController;
use App\Http\Controllers\PublicChatController;

// AI/Ollama API routes
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

// Routes API publiques pour l'interface frontend React
Route::prefix('public')->name('api.public.')->group(function () {

    // Records
    Route::get('records', [PublicRecordController::class, 'apiIndex'])->name('records.index');
    Route::get('records/{record}', [PublicRecordController::class, 'apiShow'])->name('records.show');
    Route::post('records/search', [PublicRecordController::class, 'apiSearch'])->name('records.search');
    Route::get('records/export', [PublicRecordController::class, 'apiExport'])->name('records.export');

    // Events
    Route::get('events', [PublicEventController::class, 'apiIndex'])->name('events.index');
    Route::get('events/{event}', [PublicEventController::class, 'apiShow'])->name('events.show');
    Route::post('events/{event}/register', [PublicEventController::class, 'apiRegister'])->name('events.register');
    Route::get('events/{event}/registrations', [PublicEventController::class, 'apiRegistrations'])->name('events.registrations');

    // News
    Route::get('news', [PublicNewsController::class, 'apiIndex'])->name('news.index');
    Route::get('news/{news}', [PublicNewsController::class, 'apiShow'])->name('news.show');
    Route::get('news/latest', [PublicNewsController::class, 'apiLatest'])->name('news.latest');

    // Search
    Route::get('search/suggestions', [PublicRecordController::class, 'apiSearchSuggestions'])->name('search.suggestions');
    Route::get('search/popular', [PublicRecordController::class, 'apiPopularSearches'])->name('search.popular');
    Route::post('records/search/facets', [PublicRecordController::class, 'apiSearchWithFacets'])->name('records.search.facets');
    Route::post('records/export/search', [PublicRecordController::class, 'apiExportSearch'])->name('records.export.search');

    // Users (Authentication)
    Route::post('users/login', [PublicUserController::class, 'apiLogin'])->name('users.login');
    Route::post('users/register', [PublicUserController::class, 'apiRegister'])->name('users.register');
    Route::post('users/logout', [PublicUserController::class, 'apiLogout'])->name('users.logout')->middleware('auth:sanctum');
    Route::post('users/verify-token', [PublicUserController::class, 'apiVerifyToken'])->name('users.verify-token');
    Route::post('users/forgot-password', [PublicUserController::class, 'apiForgotPassword'])->name('users.forgot-password');
    Route::post('users/reset-password', [PublicUserController::class, 'apiResetPassword'])->name('users.reset-password');
    Route::patch('users/profile', [PublicUserController::class, 'apiUpdateProfile'])->name('users.update-profile')->middleware('auth:sanctum');

    // Document Requests
    Route::post('documents/request', [PublicDocumentRequestController::class, 'apiStore'])->name('documents.request');
    Route::get('documents/requests', [PublicDocumentRequestController::class, 'apiIndex'])->name('documents.requests')->middleware('auth:sanctum');
    Route::get('documents/requests/{request}', [PublicDocumentRequestController::class, 'apiShow'])->name('documents.requests.show')->middleware('auth:sanctum');

    // Feedback
    Route::post('feedback', [PublicFeedbackController::class, 'apiStore'])->name('feedback.store');
    Route::get('feedback', [PublicFeedbackController::class, 'apiIndex'])->name('feedback.index')->middleware('auth:sanctum');

    // Chat
    Route::get('chat/conversations', [PublicChatController::class, 'apiConversations'])->name('chat.conversations')->middleware('auth:sanctum');
    Route::post('chat/conversations', [PublicChatController::class, 'apiCreateConversation'])->name('chat.conversations.create')->middleware('auth:sanctum');
    Route::get('chat/conversations/{conversation}/messages', [PublicChatController::class, 'apiMessages'])->name('chat.messages')->middleware('auth:sanctum');
    Route::post('chat/conversations/{conversation}/messages', [PublicChatController::class, 'apiSendMessage'])->name('chat.messages.send')->middleware('auth:sanctum');
});
