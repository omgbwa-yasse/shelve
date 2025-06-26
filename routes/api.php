<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AiModelController;
use App\Http\Controllers\AiInteractionController;
use App\Http\Controllers\AiJobController;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\Api\PublicRecordApiController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicUserController;
use App\Http\Controllers\PublicDocumentRequestController;
use App\Http\Controllers\PublicFeedbackController;
use App\Http\Controllers\PublicChatController;



// Routes API publiques pour l'interface frontend React
Route::prefix('public')->name('api.public.')->group(function () {

    // Records - New API endpoints
    Route::get('records', [PublicRecordApiController::class, 'index'])->name('records.index');
    Route::get('records/{record}', [PublicRecordApiController::class, 'show'])->name('records.show');
    Route::post('records/search', [PublicRecordApiController::class, 'search'])->name('records.search');
    Route::get('records/export', [PublicRecordApiController::class, 'export'])->name('records.export');

    // Events
    Route::get('events', [PublicEventController::class, 'apiIndex'])->name('events.index');
    Route::get('events/{event}', [PublicEventController::class, 'apiShow'])->name('events.show');
    Route::post('events/{event}/register', [PublicEventController::class, 'apiRegister'])->name('events.register');
    Route::get('events/{event}/registrations', [PublicEventController::class, 'apiRegistrations'])->name('events.registrations');

    // News
    Route::get('news', [PublicNewsController::class, 'apiIndex'])->name('news.index');
    Route::get('news/latest', [PublicNewsController::class, 'apiLatest'])->name('news.latest');
    Route::get('news/{news}', [PublicNewsController::class, 'apiShow'])->name('news.show');

    // Search - New API endpoints
    Route::get('search/suggestions', [PublicRecordApiController::class, 'suggestions'])->name('search.suggestions');
    Route::get('search/popular', [PublicRecordApiController::class, 'popularSearches'])->name('search.popular');
    Route::get('records/statistics', [PublicRecordApiController::class, 'statistics'])->name('records.statistics');
    Route::get('records/filters', [PublicRecordApiController::class, 'filters'])->name('records.filters');
    Route::post('records/export/search', [PublicRecordApiController::class, 'exportSearch'])->name('records.export.search');

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
