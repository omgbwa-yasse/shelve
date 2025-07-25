<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AiModelController;
use App\Http\Controllers\AiInteractionController;
use App\Http\Controllers\AiJobController;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\RecordEnricherController;
use App\Http\Controllers\McpProxyController;
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
use App\Http\Controllers\Api\SettingsApiController;
use App\Http\Controllers\ThesaurusController;
use App\Http\Controllers\ExternalContactController;
use App\Http\Controllers\ExternalOrganizationController;

// Routes API pour l'enrichissement des records via Ollama MCP
Route::prefix('records/enrich')->name('api.records.enrich.')->middleware('auth:sanctum')->group(function () {
    Route::get('status', [RecordEnricherController::class, 'status'])->name('status');
    Route::post('{id}', [RecordEnricherController::class, 'enrich'])->name('process');
    Route::post('{id}/preview', [RecordEnricherController::class, 'preview'])->name('preview');
    Route::post('{id}/format-title', [RecordEnricherController::class, 'formatTitle'])->name('format-title');
    Route::post('{id}/extract-keywords', [RecordEnricherController::class, 'extractKeywords'])->name('extract-keywords');
    Route::post('{id}/categorized-keywords', [RecordEnricherController::class, 'extractCategorizedKeywords'])->name('categorized-keywords');
});

// Routes API pour le proxy MCP
Route::prefix('mcp')->name('api.mcp.')->middleware('auth:sanctum')->group(function () {
    Route::post('enrich/{id}', [McpProxyController::class, 'enrich'])->name('enrich');
    Route::post('extract-keywords/{id}', [McpProxyController::class, 'extractKeywords'])->name('extract-keywords');
    Route::post('validate/{id}', [McpProxyController::class, 'validateRecord'])->name('validate');
    Route::post('classify/{id}', [McpProxyController::class, 'classify'])->name('classify');
    Route::post('report/{id}', [McpProxyController::class, 'report'])->name('report');
});

// Routes API pour la gestion des paramètres système (utilisé par MCP)
Route::prefix('settings')->name('api.settings.')->middleware('auth:sanctum')->group(function () {
    Route::get('{name}', [SettingsApiController::class, 'getSetting'])->name('get');
    Route::post('batch', [SettingsApiController::class, 'getSettings'])->name('batch');
    Route::get('categories/ai', [SettingsApiController::class, 'getAiSettings'])->name('ai');
    Route::put('{name}', [SettingsApiController::class, 'updateSetting'])->name('update');
    Route::get('test/providers', [SettingsApiController::class, 'testAiProviders'])->name('test-providers');
});

// Routes API pour l'analyse des documents numériques (attachments)
Route::prefix('attachments')->name('api.attachments.')->middleware('auth:sanctum')->group(function () {
    Route::post('metadata', [App\Http\Controllers\Api\AttachmentApiController::class, 'metadata'])->name('metadata');
    Route::get('{id}/metadata', [App\Http\Controllers\Api\AttachmentApiController::class, 'singleMetadata'])->name('single-metadata');
    Route::post('extract-content', [App\Http\Controllers\Api\AttachmentApiController::class, 'extractContent'])->name('extract-content');
    Route::post('upload', [App\Http\Controllers\Api\AttachmentApiController::class, 'upload'])->name('upload');
    Route::get('/', [App\Http\Controllers\Api\AttachmentApiController::class, 'index'])->name('index');
    Route::get('health', [App\Http\Controllers\Api\AttachmentApiController::class, 'health'])->name('health');
});



// Routes API pour les records et leurs relations avec le thésaurus
Route::prefix('records')->name('api.records.')->middleware('auth:sanctum')->group(function () {
    Route::get('{record}/terms', [ThesaurusController::class, 'apiRecordTerms'])->name('terms');
    Route::post('{record}/terms', [ThesaurusController::class, 'apiAssociateTerms'])->name('associate-terms');
    Route::delete('{record}/terms/{concept}', [ThesaurusController::class, 'apiDisassociateTerm'])->name('disassociate-term');
});




// Routes API publiques pour l'interface frontend React
Route::prefix('public')->name('api.public.')->group(function () {

    // Records
    Route::apiResource('records', PublicRecordApiController::class)->only(['index', 'show']);
    Route::post('records/search', [PublicRecordApiController::class, 'search'])->name('records.search');
    Route::get('records/autocomplete', [PublicRecordApiController::class, 'autocomplete'])->name('records.autocomplete');
    Route::get('records/export', [PublicRecordApiController::class, 'export'])->name('records.export');
    Route::get('records/statistics', [PublicRecordApiController::class, 'statistics'])->name('records.statistics');
    Route::get('records/filters', [PublicRecordApiController::class, 'filters'])->name('records.filters');
    Route::post('records/export/search', [PublicRecordApiController::class, 'exportSearch'])->name('records.export.search');

    // Events
    Route::apiResource('events', PublicEventApiController::class)->only(['index', 'show']);

    // News
    Route::apiResource('news', PublicNewsApiController::class)->only(['index', 'show']);
    Route::get('news/latest', [PublicNewsApiController::class, 'latest'])->name('news.latest');

    // Search
    Route::get('search/suggestions', [PublicRecordApiController::class, 'suggestions'])->name('search.suggestions');
    Route::get('search/popular', [PublicRecordApiController::class, 'popularSearches'])->name('search.popular');

    // Users (Authentication)
    Route::post('users/login', [PublicUserApiController::class, 'login'])->name('users.login');
    Route::post('users/register', [PublicUserApiController::class, 'register'])->name('users.register');
    Route::post('users/logout', [PublicUserApiController::class, 'logout'])->name('users.logout')->middleware('auth:sanctum');
    Route::post('users/verify-token', [PublicUserApiController::class, 'verifyToken'])->name('users.verify-token');
    Route::post('users/forgot-password', [PublicUserApiController::class, 'forgotPassword'])->name('users.forgot-password');
    Route::post('users/reset-password', [PublicUserApiController::class, 'resetPassword'])->name('users.reset-password');


    // Feedback
    Route::apiResource('feedback', PublicFeedbackApiController::class)->only(['index', 'store']);
    Route::get('feedback', [PublicFeedbackApiController::class, 'index'])->name('feedback.index')->middleware('auth:sanctum');

    // Chat
    Route::apiResource('chat/conversations', PublicChatApiController::class)->only(['index', 'store'])->middleware('auth:sanctum');
    Route::get('chat/conversations/{conversation}/messages', [PublicChatApiController::class, 'messages'])->name('chat.messages')->middleware('auth:sanctum');
    Route::post('chat/conversations/{conversation}/messages', [PublicChatApiController::class, 'sendMessage'])->name('chat.messages.send')->middleware('auth:sanctum');

    // Pages
    Route::apiResource('pages', PublicPageApiController::class)->except(['create', 'edit']);
    Route::get('pages/published', [PublicPageApiController::class, 'published'])->name('pages.published');
    Route::get('pages/slug/{slug}', [PublicPageApiController::class, 'showBySlug'])->name('pages.show-by-slug');



    // Search Logs
    Route::apiResource('search-logs', PublicSearchLogApiController::class)->only(['index', 'store']);
    Route::get('search-logs/statistics', [PublicSearchLogApiController::class, 'statistics'])->name('search-logs.statistics')->middleware('auth:sanctum');
    Route::get('search-logs/user-history', [PublicSearchLogApiController::class, 'userHistory'])->name('search-logs.user-history')->middleware('auth:sanctum');


});







// Routes API pour l'interface administrative
Route::middleware('auth:sanctum')->group(function () {


    // User
    Route::patch('users/profile', [PublicUserApiController::class, 'updateProfile'])->name('users.update-profile')->middleware('auth:sanctum');

     // Templates
    Route::apiResource('templates', PublicTemplateApiController::class)->except('templates');
    Route::get('templates/type/{type}', [PublicTemplateApiController::class, 'byType'])->name('templates.by-type');


    // Events
    Route::apiResource('events', PublicEventApiController::class)->names('events');
    Route::post('events/{event}/register', [PublicEventApiController::class, 'register'])->name('events.register');
    Route::delete('events/{event}/register', [PublicEventApiController::class, 'cancelRegistration'])->name('events.cancel-registration');
    Route::get('events/{event}/registrations', [PublicEventApiController::class, 'registrations'])->name('events.registrations');


    // Document Requests
    Route::post('documents/request', [PublicDocumentRequestApiController::class, 'store'])->name('documents.request');
    Route::apiResource('documents/requests', PublicDocumentRequestApiController::class)->only(['index', 'show'])->middleware('auth:sanctum');


     // Responses
    Route::apiResource('responses', PublicResponseApiController::class)->middleware('auth:sanctum');
    Route::patch('responses/{response}/mark-as-sent', [PublicResponseApiController::class, 'markAsSent'])->name('responses.mark-as-sent')->middleware('auth:sanctum');
    Route::get('responses/document-request/{documentRequest}', [PublicResponseApiController::class, 'byDocumentRequest'])->name('responses.by-document-request')->middleware('auth:sanctum');

    // Response Attachments
    Route::apiResource('response-attachments', PublicResponseAttachmentApiController::class)->middleware('auth:sanctum');
    Route::get('response-attachments/{attachment}/download', [PublicResponseAttachmentApiController::class, 'download'])->name('response-attachments.download')->middleware('auth:sanctum');

    // Record Attachments
    Route::apiResource('record-attachments', PublicRecordAttachmentApiController::class)->except(['update']);
    Route::get('record-attachments/{attachment}/download', [PublicRecordAttachmentApiController::class, 'download'])->name('record-attachments.download');
    Route::get('record-attachments/public-record/{publicRecord}', [PublicRecordAttachmentApiController::class, 'byPublicRecord'])->name('record-attachments.by-public-record');

    // Chat Participants
    Route::apiResource('chat-participants', PublicChatParticipantApiController::class)->middleware('auth:sanctum');
    Route::get('chat-participants/chat/{chat}', [PublicChatParticipantApiController::class, 'byChat'])->name('chat-participants.by-chat')->middleware('auth:sanctum');
    Route::get('chat-participants/user/{user}', [PublicChatParticipantApiController::class, 'byUser'])->name('chat-participants.by-user')->middleware('auth:sanctum');
    Route::patch('chat-participants/{participant}/mark-as-read', [PublicChatParticipantApiController::class, 'markAsRead'])->name('chat-participants.mark-as-read')->middleware('auth:sanctum');
    Route::patch('chat-participants/{participant}/toggle-admin', [PublicChatParticipantApiController::class, 'toggleAdmin'])->name('chat-participants.toggle-admin')->middleware('auth:sanctum');

    // Chat Messages
    Route::apiResource('chat-messages', PublicChatMessageApiController::class)->middleware('auth:sanctum');
    Route::get('chat-messages/chat/{chat}', [PublicChatMessageApiController::class, 'byChat'])->name('chat-messages.by-chat')->middleware('auth:sanctum');
    Route::get('chat-messages/user/{user}', [PublicChatMessageApiController::class, 'byUser'])->name('chat-messages.by-user')->middleware('auth:sanctum');
    Route::patch('chat-messages/{message}/mark-as-read', [PublicChatMessageApiController::class, 'markAsRead'])->name('chat-messages.mark-as-read')->middleware('auth:sanctum');
    Route::patch('chat-messages/mark-multiple-as-read', [PublicChatMessageApiController::class, 'markMultipleAsRead'])->name('chat-messages.mark-multiple-as-read')->middleware('auth:sanctum');
    Route::get('chat-messages/chat/{chat}/unread-count', [PublicChatMessageApiController::class, 'unreadCount'])->name('chat-messages.unread-count')->middleware('auth:sanctum');
    Route::post('chat-messages/search', [PublicChatMessageApiController::class, 'search'])->name('chat-messages.search')->middleware('auth:sanctum');

    // Event Registrations
    Route::apiResource('event-registrations', PublicEventRegistrationApiController::class)->middleware('auth:sanctum');
    Route::get('event-registrations/event/{event}', [PublicEventRegistrationApiController::class, 'byEvent'])->name('event-registrations.by-event')->middleware('auth:sanctum');
    Route::get('event-registrations/user/{user}', [PublicEventRegistrationApiController::class, 'byUser'])->name('event-registrations.by-user')->middleware('auth:sanctum');
    Route::patch('event-registrations/{registration}/confirm', [PublicEventRegistrationApiController::class, 'confirm'])->name('event-registrations.confirm')->middleware('auth:sanctum');
    Route::patch('event-registrations/{registration}/cancel', [PublicEventRegistrationApiController::class, 'cancel'])->name('event-registrations.cancel')->middleware('auth:sanctum');
    Route::get('event-registrations/statistics', [PublicEventRegistrationApiController::class, 'statistics'])->name('event-registrations.statistics')->middleware('auth:sanctum');
});



