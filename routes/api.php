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

// Imports des contrôleurs publics web (non-API)
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicUserController;
use App\Http\Controllers\PublicChatController;
use App\Http\Controllers\PublicChatMessageController;
use App\Http\Controllers\PublicChatParticipantController;
use App\Http\Controllers\PublicEventRegistrationController;
use App\Http\Controllers\PublicDocumentRequestController;
use App\Http\Controllers\PublicRecordController;
use App\Http\Controllers\PublicResponseController;
use App\Http\Controllers\PublicResponseAttachmentController;
use App\Http\Controllers\PublicFeedbackController;
use App\Http\Controllers\PublicSearchLogController;
use App\Http\Controllers\PublicTemplateController;

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




Route::prefix('public')->name('api.public.')->group(function () {
    // Records - Services ouverts
    Route::get('records', [PublicRecordApiController::class, 'index'])->name('records.index');
    Route::get('records/{record}', [PublicRecordApiController::class, 'show'])->name('records.show');
    Route::get('records/{record}/attachments', [PublicRecordApiController::class, 'attachments'])->name('records.attachments');


    // Records - Services ouverts
    Route::get('events', [PublicEventApiController::class, 'index'])->name('events.index');
    Route::get('events/{event}', [PublicEventApiController::class, 'show'])->name('events.show');

    // Routes pour les actualités publiques
    Route::get('news', [PublicNewsApiController::class, 'index'])->name('news.index');
    Route::get('news/{news}', [PublicNewsApiController::class, 'show'])->name('news.show');

    // Routes pour les pages publiques
    Route::get('pages', [PublicPageApiController::class, 'index'])->name('pages.index');
    Route::get('pages/{page}', [PublicPageApiController::class, 'show'])->name('pages.show');
    Route::get('pages/published', [PublicPageApiController::class, 'published'])->name('pages.published');
    Route::get('pages/slug/{slug}', [PublicPageApiController::class, 'slug'])->name('pages.slug');

    // Routes pour la recherche
    Route::get('search/suggestions', [PublicRecordApiController::class, 'suggestions'])->name('search.suggestions');
    Route::get('search/popular', [PublicRecordApiController::class, 'popularSearches'])->name('search.popular');

    // Users (Authentication)
    Route::post('users/login', [PublicUserApiController::class, 'login'])->name('users.login');
    Route::post('users/register', [PublicUserApiController::class, 'register'])->name('users.register');
    Route::post('users/verify-token', [PublicUserApiController::class, 'verifyToken'])->name('users.verify-token');
    Route::post('users/forgot-password', [PublicUserApiController::class, 'forgotPassword'])->name('users.forgot-password');
    Route::post('users/reset-password', [PublicUserApiController::class, 'resetPassword'])->name('users.reset-password');

    // Feedback
    Route::post('feedback', [PublicFeedbackApiController::class, 'store'])->name('feedback.store');
    Route::post('search-logs', [PublicSearchLogApiController::class, 'store'])->name('search-logs.store');
});



Route::prefix('public')->name('api.secure.public.')->middleware('auth:sanctum')->group(function () {
    Route::get('users/{user}', [PublicUserApiController::class, 'show'])->name('users.show');

    // Gestion des discussions et messages
    Route::resource('chats', PublicChatApiController::class)->names([
        'index' => 'chats.index',
        'store' => 'chats.store',
        'show' => 'chats.show',
        'update' => 'chats.update',
        'destroy' => 'chats.destroy',
    ]);
    Route::resource('chats.messages', PublicChatMessageController::class)->names([
        'index' => 'chats.messages.index',
        'store' => 'chats.messages.store',
        'show' => 'chats.messages.show',
        'update' => 'chats.messages.update',
        'destroy' => 'chats.messages.destroy',
    ]);
    Route::resource('chats.participants', PublicChatParticipantController::class)->names([
        'index' => 'chats.participants.index',
        'store' => 'chats.participants.store',
        'show' => 'chats.participants.show',
        'update' => 'chats.participants.update',
        'destroy' => 'chats.participants.destroy',
    ]);

    // Gestion des événements publics
    Route::resource('events', PublicEventApiController::class)->names([
        'index' => 'events.index',
        'store' => 'events.store',
        'show' => 'events.show',
        'update' => 'events.update',
        'destroy' => 'events.destroy',
    ]);
    Route::resource('events.registrations', PublicEventRegistrationController::class)->names([
        'index' => 'events.registrations.index',
        'store' => 'events.registrations.store',
        'show' => 'events.registrations.show',
        'update' => 'events.registrations.update',
        'destroy' => 'events.registrations.destroy',
    ]);

    // Gestion du contenu public
    Route::resource('news', PublicNewsApiController::class)->names([
        'index' => 'news.index',
        'store' => 'news.store',
        'show' => 'news.show',
        'update' => 'news.update',
        'destroy' => 'news.destroy',
    ]);
    Route::resource('pages', PublicPageApiController::class)->names([
        'index' => 'pages.index',
        'store' => 'pages.store',
        'show' => 'pages.show',
        'update' => 'pages.update',
        'destroy' => 'pages.destroy',
    ]);
    Route::resource('templates', PublicTemplateApiController::class)->names([
        'index' => 'templates.index',
        'store' => 'templates.store',
        'show' => 'templates.show',
        'update' => 'templates.update',
        'destroy' => 'templates.destroy',
    ]);

    // Gestion des demandes de documents
    Route::resource('document-requests', PublicDocumentRequestApiController::class)->names([
        'index' => 'document-requests.index',
        'store' => 'document-requests.store',
        'show' => 'document-requests.show',
        'update' => 'document-requests.update',
        'destroy' => 'document-requests.destroy',
    ]);
    Route::get('records/autocomplete', [PublicRecordApiController::class, 'autocomplete'])->name('records.autocomplete');
    Route::resource('records', PublicRecordApiController::class)->names([
        'index' => 'records.index',
        'store' => 'records.store',
        'show' => 'records.show',
        'update' => 'records.update',
        'destroy' => 'records.destroy',
    ]);
    Route::resource('responses', PublicResponseApiController::class)->names([
        'index' => 'responses.index',
        'store' => 'responses.store',
        'show' => 'responses.show',
        'update' => 'responses.update',
        'destroy' => 'responses.destroy',
    ]);
    Route::resource('response-attachments', PublicResponseAttachmentApiController::class)->names([
        'index' => 'response-attachments.index',
        'store' => 'response-attachments.store',
        'show' => 'response-attachments.show',
        'update' => 'response-attachments.update',
        'destroy' => 'response-attachments.destroy',
    ]);

    // Gestion des retours et recherches
    Route::resource('feedback', PublicFeedbackApiController::class)->names([
        'index' => 'feedback.index',
        'store' => 'feedback.store',
        'show' => 'feedback.show',
        'update' => 'feedback.update',
        'destroy' => 'feedback.destroy',
    ]);
    Route::put('feedback/{feedback}/status', [PublicFeedbackApiController::class, 'updateStatus'])->name('feedback.update-status');
    Route::post('feedback/{feedback}/comments', [PublicFeedbackApiController::class, 'addComment'])->name('feedback.add-comment');
    Route::delete('feedback/{feedback}/comments/{comment}', [PublicFeedbackApiController::class, 'deleteComment'])->name('feedback.delete-comment');
    Route::resource('search-logs', PublicSearchLogApiController::class)->only(['index', 'show'])->names([
        'index' => 'search-logs.index',
        'show' => 'search-logs.show',
    ]);

});







