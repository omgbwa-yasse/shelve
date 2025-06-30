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
use App\Http\Controllers\Api\PublicPageApiController;
use App\Http\Controllers\Api\PublicTemplateApiController;
use App\Http\Controllers\Api\PublicSearchLogApiController;
use App\Http\Controllers\Api\PublicResponseApiController;
use App\Http\Controllers\Api\PublicResponseAttachmentApiController;
use App\Http\Controllers\Api\PublicRecordAttachmentApiController;
use App\Http\Controllers\Api\PublicChatParticipantApiController;
use App\Http\Controllers\Api\PublicChatMessageApiController;
use App\Http\Controllers\Api\PublicEventRegistrationApiController;
use App\Http\Controllers\Api\OrganisationApiController;

// Routes API publiques pour l'interface frontend React
Route::prefix('public')->name('api.public.')->group(function () {

    // Handle CORS preflight requests
    Route::options('{any}', function () {
        return response('', 200);
    })->where('any', '.*');

    // Records - New API endpoints
    Route::get('records', [PublicRecordApiController::class, 'index'])->name('records.index');
    Route::get('records/{record}', [PublicRecordApiController::class, 'show'])->name('records.show');
    Route::post('records/search', [PublicRecordApiController::class, 'search'])->name('records.search');
    Route::get('records/autocomplete', [PublicRecordApiController::class, 'autocomplete'])->name('records.autocomplete');
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
    Route::post('users/logout', [PublicUserApiController::class, 'logout'])->name('users.logout')->middleware('auth:sanctum');
    Route::post('users/verify-token', [PublicUserApiController::class, 'verifyToken'])->name('users.verify-token');
    Route::post('users/forgot-password', [PublicUserApiController::class, 'forgotPassword'])->name('users.forgot-password');
    Route::post('users/reset-password', [PublicUserApiController::class, 'resetPassword'])->name('users.reset-password');
    Route::patch('users/profile', [PublicUserApiController::class, 'updateProfile'])->name('users.update-profile')->middleware('auth:sanctum');

    // Document Requests
    Route::post('documents/request', [PublicDocumentRequestApiController::class, 'store'])->name('documents.request');
    Route::get('documents/requests', [PublicDocumentRequestApiController::class, 'index'])->name('documents.requests')->middleware('auth:sanctum');
    Route::get('documents/requests/{request}', [PublicDocumentRequestApiController::class, 'show'])->name('documents.requests.show')->middleware('auth:sanctum');

    // Feedback
    Route::post('feedback', [PublicFeedbackApiController::class, 'store'])->name('feedback.store');
    Route::get('feedback', [PublicFeedbackApiController::class, 'index'])->name('feedback.index')->middleware('auth:sanctum');

    // Chat
    Route::get('chat/conversations', [PublicChatApiController::class, 'conversations'])->name('chat.conversations')->middleware('auth:sanctum');
    Route::post('chat/conversations', [PublicChatApiController::class, 'createConversation'])->name('chat.conversations.create')->middleware('auth:sanctum');
    Route::get('chat/conversations/{conversation}/messages', [PublicChatApiController::class, 'messages'])->name('chat.messages')->middleware('auth:sanctum');
    Route::post('chat/conversations/{conversation}/messages', [PublicChatApiController::class, 'sendMessage'])->name('chat.messages.send')->middleware('auth:sanctum');

    // Pages
    Route::get('pages', [PublicPageApiController::class, 'index'])->name('pages.index');
    Route::get('pages/published', [PublicPageApiController::class, 'published'])->name('pages.published');
    Route::get('pages/slug/{slug}', [PublicPageApiController::class, 'showBySlug'])->name('pages.show-by-slug');
    Route::get('pages/{page}', [PublicPageApiController::class, 'show'])->name('pages.show');
    Route::post('pages', [PublicPageApiController::class, 'store'])->name('pages.store')->middleware('auth:sanctum');
    Route::put('pages/{page}', [PublicPageApiController::class, 'update'])->name('pages.update')->middleware('auth:sanctum');
    Route::delete('pages/{page}', [PublicPageApiController::class, 'destroy'])->name('pages.destroy')->middleware('auth:sanctum');

    // Templates
    Route::get('templates', [PublicTemplateApiController::class, 'index'])->name('templates.index')->middleware('auth:sanctum');
    Route::get('templates/type/{type}', [PublicTemplateApiController::class, 'byType'])->name('templates.by-type');
    Route::get('templates/{template}', [PublicTemplateApiController::class, 'show'])->name('templates.show')->middleware('auth:sanctum');
    Route::post('templates', [PublicTemplateApiController::class, 'store'])->name('templates.store')->middleware('auth:sanctum');
    Route::put('templates/{template}', [PublicTemplateApiController::class, 'update'])->name('templates.update')->middleware('auth:sanctum');
    Route::delete('templates/{template}', [PublicTemplateApiController::class, 'destroy'])->name('templates.destroy')->middleware('auth:sanctum');

    // Search Logs
    Route::get('search-logs', [PublicSearchLogApiController::class, 'index'])->name('search-logs.index')->middleware('auth:sanctum');
    Route::post('search-logs', [PublicSearchLogApiController::class, 'store'])->name('search-logs.store');
    Route::get('search-logs/statistics', [PublicSearchLogApiController::class, 'statistics'])->name('search-logs.statistics')->middleware('auth:sanctum');
    Route::get('search-logs/user-history', [PublicSearchLogApiController::class, 'userHistory'])->name('search-logs.user-history')->middleware('auth:sanctum');

    // Responses
    Route::get('responses', [PublicResponseApiController::class, 'index'])->name('responses.index')->middleware('auth:sanctum');
    Route::get('responses/{response}', [PublicResponseApiController::class, 'show'])->name('responses.show')->middleware('auth:sanctum');
    Route::post('responses', [PublicResponseApiController::class, 'store'])->name('responses.store')->middleware('auth:sanctum');
    Route::put('responses/{response}', [PublicResponseApiController::class, 'update'])->name('responses.update')->middleware('auth:sanctum');
    Route::delete('responses/{response}', [PublicResponseApiController::class, 'destroy'])->name('responses.destroy')->middleware('auth:sanctum');
    Route::patch('responses/{response}/mark-as-sent', [PublicResponseApiController::class, 'markAsSent'])->name('responses.mark-as-sent')->middleware('auth:sanctum');
    Route::get('responses/document-request/{documentRequest}', [PublicResponseApiController::class, 'byDocumentRequest'])->name('responses.by-document-request')->middleware('auth:sanctum');

    // Response Attachments
    Route::get('response-attachments', [PublicResponseAttachmentApiController::class, 'index'])->name('response-attachments.index')->middleware('auth:sanctum');
    Route::get('response-attachments/{attachment}', [PublicResponseAttachmentApiController::class, 'show'])->name('response-attachments.show')->middleware('auth:sanctum');
    Route::post('response-attachments', [PublicResponseAttachmentApiController::class, 'store'])->name('response-attachments.store')->middleware('auth:sanctum');
    Route::delete('response-attachments/{attachment}', [PublicResponseAttachmentApiController::class, 'destroy'])->name('response-attachments.destroy')->middleware('auth:sanctum');
    Route::get('response-attachments/{attachment}/download', [PublicResponseAttachmentApiController::class, 'download'])->name('response-attachments.download')->middleware('auth:sanctum');

    // Record Attachments
    Route::get('record-attachments', [PublicRecordAttachmentApiController::class, 'index'])->name('record-attachments.index');
    Route::get('record-attachments/{attachment}', [PublicRecordAttachmentApiController::class, 'show'])->name('record-attachments.show');
    Route::post('record-attachments', [PublicRecordAttachmentApiController::class, 'store'])->name('record-attachments.store')->middleware('auth:sanctum');
    Route::delete('record-attachments/{attachment}', [PublicRecordAttachmentApiController::class, 'destroy'])->name('record-attachments.destroy')->middleware('auth:sanctum');
    Route::get('record-attachments/{attachment}/download', [PublicRecordAttachmentApiController::class, 'download'])->name('record-attachments.download');
    Route::get('record-attachments/public-record/{publicRecord}', [PublicRecordAttachmentApiController::class, 'byPublicRecord'])->name('record-attachments.by-public-record');

    // Chat Participants
    Route::get('chat-participants', [PublicChatParticipantApiController::class, 'index'])->name('chat-participants.index')->middleware('auth:sanctum');
    Route::get('chat-participants/{participant}', [PublicChatParticipantApiController::class, 'show'])->name('chat-participants.show')->middleware('auth:sanctum');
    Route::post('chat-participants', [PublicChatParticipantApiController::class, 'store'])->name('chat-participants.store')->middleware('auth:sanctum');
    Route::put('chat-participants/{participant}', [PublicChatParticipantApiController::class, 'update'])->name('chat-participants.update')->middleware('auth:sanctum');
    Route::delete('chat-participants/{participant}', [PublicChatParticipantApiController::class, 'destroy'])->name('chat-participants.destroy')->middleware('auth:sanctum');
    Route::get('chat-participants/chat/{chat}', [PublicChatParticipantApiController::class, 'byChat'])->name('chat-participants.by-chat')->middleware('auth:sanctum');
    Route::get('chat-participants/user/{user}', [PublicChatParticipantApiController::class, 'byUser'])->name('chat-participants.by-user')->middleware('auth:sanctum');
    Route::patch('chat-participants/{participant}/mark-as-read', [PublicChatParticipantApiController::class, 'markAsRead'])->name('chat-participants.mark-as-read')->middleware('auth:sanctum');
    Route::patch('chat-participants/{participant}/toggle-admin', [PublicChatParticipantApiController::class, 'toggleAdmin'])->name('chat-participants.toggle-admin')->middleware('auth:sanctum');

    // Chat Messages
    Route::get('chat-messages', [PublicChatMessageApiController::class, 'index'])->name('chat-messages.index')->middleware('auth:sanctum');
    Route::get('chat-messages/{message}', [PublicChatMessageApiController::class, 'show'])->name('chat-messages.show')->middleware('auth:sanctum');
    Route::post('chat-messages', [PublicChatMessageApiController::class, 'store'])->name('chat-messages.store')->middleware('auth:sanctum');
    Route::put('chat-messages/{message}', [PublicChatMessageApiController::class, 'update'])->name('chat-messages.update')->middleware('auth:sanctum');
    Route::delete('chat-messages/{message}', [PublicChatMessageApiController::class, 'destroy'])->name('chat-messages.destroy')->middleware('auth:sanctum');
    Route::get('chat-messages/chat/{chat}', [PublicChatMessageApiController::class, 'byChat'])->name('chat-messages.by-chat')->middleware('auth:sanctum');
    Route::get('chat-messages/user/{user}', [PublicChatMessageApiController::class, 'byUser'])->name('chat-messages.by-user')->middleware('auth:sanctum');
    Route::patch('chat-messages/{message}/mark-as-read', [PublicChatMessageApiController::class, 'markAsRead'])->name('chat-messages.mark-as-read')->middleware('auth:sanctum');
    Route::patch('chat-messages/mark-multiple-as-read', [PublicChatMessageApiController::class, 'markMultipleAsRead'])->name('chat-messages.mark-multiple-as-read')->middleware('auth:sanctum');
    Route::get('chat-messages/chat/{chat}/unread-count', [PublicChatMessageApiController::class, 'unreadCount'])->name('chat-messages.unread-count')->middleware('auth:sanctum');
    Route::post('chat-messages/search', [PublicChatMessageApiController::class, 'search'])->name('chat-messages.search')->middleware('auth:sanctum');

    // Event Registrations
    Route::get('event-registrations', [PublicEventRegistrationApiController::class, 'index'])->name('event-registrations.index')->middleware('auth:sanctum');
    Route::get('event-registrations/{registration}', [PublicEventRegistrationApiController::class, 'show'])->name('event-registrations.show')->middleware('auth:sanctum');
    Route::post('event-registrations', [PublicEventRegistrationApiController::class, 'store'])->name('event-registrations.store');
    Route::put('event-registrations/{registration}', [PublicEventRegistrationApiController::class, 'update'])->name('event-registrations.update')->middleware('auth:sanctum');
    Route::delete('event-registrations/{registration}', [PublicEventRegistrationApiController::class, 'destroy'])->name('event-registrations.destroy')->middleware('auth:sanctum');
    Route::get('event-registrations/event/{event}', [PublicEventRegistrationApiController::class, 'byEvent'])->name('event-registrations.by-event')->middleware('auth:sanctum');
    Route::get('event-registrations/user/{user}', [PublicEventRegistrationApiController::class, 'byUser'])->name('event-registrations.by-user')->middleware('auth:sanctum');
    Route::patch('event-registrations/{registration}/confirm', [PublicEventRegistrationApiController::class, 'confirm'])->name('event-registrations.confirm')->middleware('auth:sanctum');
    Route::patch('event-registrations/{registration}/cancel', [PublicEventRegistrationApiController::class, 'cancel'])->name('event-registrations.cancel')->middleware('auth:sanctum');
    Route::get('event-registrations/statistics', [PublicEventRegistrationApiController::class, 'statistics'])->name('event-registrations.statistics')->middleware('auth:sanctum');
});

// Routes API pour l'interface administrative
Route::middleware('auth:sanctum')->group(function () {
    // Organisations
    Route::get('organisations/{organisation}/users', [OrganisationApiController::class, 'getUsers'])->name('api.organisations.users');
});
