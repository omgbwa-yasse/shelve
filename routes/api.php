<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AiModelController;
use App\Http\Controllers\AiInteractionController;
use App\Http\Controllers\AiJobController;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\Api\PublicRecordApiController;
use App\Http\Controllers\Api\PublicEventApiController;
use App\Http\Controllers\Api\PublicNewsApiController;
use App\Http\Controllers\Api\PublicUserApiController;
use App\Http\Controllers\Api\PublicDocumentRequestApiController;
use App\Http\Controllers\Api\PublicFeedbackApiController;
use App\Http\Controllers\Api\PublicChatApiController;

// Middleware constants
const AUTH_SANCTUM = 'auth:sanctum';



// Routes API publiques pour l'interface frontend React
Route::prefix('public')->name('api.public.')->group(function () {

    // Records - New API endpoints
    Route::get('records', [PublicRecordApiController::class, 'index'])->name('records.index');
    Route::get('records/{record}', [PublicRecordApiController::class, 'show'])->name('records.show');
    Route::post('records/search', [PublicRecordApiController::class, 'search'])->name('records.search');
    Route::get('records/export', [PublicRecordApiController::class, 'export'])->name('records.export');

    // Events
    Route::get('events', [PublicEventApiController::class, 'index'])->name('events.index');
    Route::get('events/{event}', [PublicEventApiController::class, 'show'])->name('events.show');
    Route::post('events/{event}/register', [PublicEventApiController::class, 'register'])->name('events.register');
    Route::delete('events/{event}/register', [PublicEventApiController::class, 'cancelRegistration'])->name('events.cancel-registration');
    Route::get('events/{event}/registrations', [PublicEventApiController::class, 'registrations'])->name('events.registrations');

    // News
    Route::get('news', [PublicNewsApiController::class, 'index'])->name('news.index');
    Route::get('news/latest', [PublicNewsApiController::class, 'latest'])->name('news.latest');
    Route::get('news/{news}', [PublicNewsApiController::class, 'show'])->name('news.show');

    // Search - New API endpoints
    Route::get('search/suggestions', [PublicRecordApiController::class, 'suggestions'])->name('search.suggestions');
    Route::get('search/popular', [PublicRecordApiController::class, 'popularSearches'])->name('search.popular');
    Route::get('records/statistics', [PublicRecordApiController::class, 'statistics'])->name('records.statistics');
    Route::get('records/filters', [PublicRecordApiController::class, 'filters'])->name('records.filters');
    Route::post('records/export/search', [PublicRecordApiController::class, 'exportSearch'])->name('records.export.search');

    // Users (Authentication)
    Route::post('users/login', [PublicUserApiController::class, 'login'])->name('users.login');
    Route::post('users/register', [PublicUserApiController::class, 'register'])->name('users.register');
    Route::post('users/logout', [PublicUserApiController::class, 'logout'])->name('users.logout')->middleware(AUTH_SANCTUM);
    Route::post('users/verify-token', [PublicUserApiController::class, 'verifyToken'])->name('users.verify-token');
    Route::post('users/forgot-password', [PublicUserApiController::class, 'forgotPassword'])->name('users.forgot-password');
    Route::post('users/reset-password', [PublicUserApiController::class, 'resetPassword'])->name('users.reset-password');
    Route::patch('users/profile', [PublicUserApiController::class, 'updateProfile'])->name('users.update-profile')->middleware(AUTH_SANCTUM);

    // Document Requests
    Route::post('documents/request', [PublicDocumentRequestApiController::class, 'store'])->name('documents.request');
    Route::get('documents/requests', [PublicDocumentRequestApiController::class, 'index'])->name('documents.requests')->middleware(AUTH_SANCTUM);
    Route::get('documents/requests/{request}', [PublicDocumentRequestApiController::class, 'show'])->name('documents.requests.show')->middleware(AUTH_SANCTUM);

    // Feedback
    Route::post('feedback', [PublicFeedbackApiController::class, 'store'])->name('feedback.store');
    Route::get('feedback', [PublicFeedbackApiController::class, 'index'])->name('feedback.index')->middleware(AUTH_SANCTUM);

    // Chat
    Route::get('chat/conversations', [PublicChatApiController::class, 'conversations'])->name('chat.conversations')->middleware(AUTH_SANCTUM);
    Route::post('chat/conversations', [PublicChatApiController::class, 'createConversation'])->name('chat.conversations.create')->middleware(AUTH_SANCTUM);
    Route::get('chat/conversations/{conversation}/messages', [PublicChatApiController::class, 'messages'])->name('chat.messages')->middleware(AUTH_SANCTUM);
    Route::post('chat/conversations/{conversation}/messages', [PublicChatApiController::class, 'sendMessage'])->name('chat.messages.send')->middleware(AUTH_SANCTUM);
});
