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
use App\Http\Controllers\ThesaurusToolController;
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

// Routes API pour le thésaurus
Route::prefix('thesaurus')->name('api.thesaurus.')->middleware('auth:sanctum')->group(function () {
    Route::get('schemes', [ThesaurusToolController::class, 'apiSchemes'])->name('schemes');
    Route::get('concepts', [ThesaurusToolController::class, 'apiConcepts'])->name('concepts');
    Route::get('concepts/autocomplete', [ThesaurusToolController::class, 'apiConceptsAutocomplete'])->name('concepts.autocomplete');
    Route::get('schemes/{scheme}/concepts', [ThesaurusToolController::class, 'apiSchemesConcepts'])->name('schemes.concepts');
});

// Routes API pour les records et leurs relations avec le thésaurus
Route::prefix('records')->name('api.records.')->middleware('auth:sanctum')->group(function () {
    Route::get('{record}/terms', [ThesaurusToolController::class, 'apiRecordTerms'])->name('terms');
    Route::post('{record}/terms', [ThesaurusToolController::class, 'apiAssociateTerms'])->name('associate-terms');
    Route::delete('{record}/terms/{concept}', [ThesaurusToolController::class, 'apiDisassociateTerm'])->name('disassociate-term');
});

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
    // Placeholder pour futures routes protégées
});

// Routes API pour le proxy MCP
Route::prefix('mcp-proxy')->name('api.mcp-proxy.')->middleware('auth:sanctum')->group(function () {
    Route::post('ask', [McpProxyController::class, 'ask'])->name('ask');
    Route::post('chat', [McpProxyController::class, 'chat'])->name('chat');
    Route::post('document', [McpProxyController::class, 'document'])->name('document');
    Route::post('image', [McpProxyController::class, 'image'])->name('image');
    Route::post('audio', [McpProxyController::class, 'audio'])->name('audio');
    Route::post('video', [McpProxyController::class, 'video'])->name('video');
    Route::post('file', [McpProxyController::class, 'file'])->name('file');
    Route::post('url', [McpProxyController::class, 'url'])->name('url');
    Route::post('status', [McpProxyController::class, 'status'])->name('status');
    Route::post('restart', [McpProxyController::class, 'restart'])->name('restart');
    Route::post('stop', [McpProxyController::class, 'stop'])->name('stop');
    Route::post('start', [McpProxyController::class, 'start'])->name('start');
    Route::post('update', [McpProxyController::class, 'update'])->name('update');
    Route::post('delete', [McpProxyController::class, 'delete'])->name('delete');
    Route::post('create', [McpProxyController::class, 'create'])->name('create');
    Route::post('import', [McpProxyController::class, 'import'])->name('import');
    Route::post('export', [McpProxyController::class, 'export'])->name('export');
    Route::post('enrich', [McpProxyController::class, 'enrich'])->name('enrich');
    Route::post('synthesize', [McpProxyController::class, 'synthesize'])->name('synthesize');
    Route::post('transcribe', [McpProxyController::class, 'transcribe'])->name('transcribe');
    Route::post('translate', [McpProxyController::class, 'translate'])->name('translate');
    Route::post('summarize', [McpProxyController::class, 'summarize'])->name('summarize');
    Route::post('paraphrase', [McpProxyController::class, 'paraphrase'])->name('paraphrase');
    Route::post('sentiment', [McpProxyController::class, 'sentiment'])->name('sentiment');
    Route::post('keywords', [McpProxyController::class, 'keywords'])->name('keywords');
    Route::post('topics', [McpProxyController::class, 'topics'])->name('topics');
    Route::post('entities', [McpProxyController::class, 'entities'])->name('entities');
    Route::post('relations', [McpProxyController::class, 'relations'])->name('relations');
    Route::post('events', [McpProxyController::class, 'events'])->name('events');
    Route::post('trends', [McpProxyController::class, 'trends'])->name('trends');
    Route::post('patterns', [McpProxyController::class, 'patterns'])->name('patterns');
    Route::post('anomalies', [McpProxyController::class, 'anomalies'])->name('anomalies');
    Route::post('metrics', [McpProxyController::class, 'metrics'])->name('metrics');
    Route::post('logs', [McpProxyController::class, 'logs'])->name('logs');
    Route::post('notifications', [McpProxyController::class, 'notifications'])->name('notifications');
    Route::post('webhooks', [McpProxyController::class, 'webhooks'])->name('webhooks');
    Route::post('tasks', [McpProxyController::class, 'tasks'])->name('tasks');
    Route::post('jobs', [McpProxyController::class, 'jobs'])->name('jobs');
    Route::post('models', [McpProxyController::class, 'models'])->name('models');
    Route::post('agents', [McpProxyController::class, 'agents'])->name('agents');
    Route::post('roles', [McpProxyController::class, 'roles'])->name('roles');
    Route::post('permissions', [McpProxyController::class, 'permissions'])->name('permissions');
    Route::post('settings', [McpProxyController::class, 'settings'])->name('settings');
    Route::post('profile', [McpProxyController::class, 'profile'])->name('profile');
    Route::post('account', [McpProxyController::class, 'account'])->name('account');
    Route::post('subscription', [McpProxyController::class, 'subscription'])->name('subscription');
    Route::post('billing', [McpProxyController::class, 'billing'])->name('billing');
    Route::post('invoices', [McpProxyController::class, 'invoices'])->name('invoices');
    Route::post('payments', [McpProxyController::class, 'payments'])->name('payments');
    Route::post('refunds', [McpProxyController::class, 'refunds'])->name('refunds');
    Route::post('coupons', [McpProxyController::class, 'coupons'])->name('coupons');
    Route::post('promotions', [McpProxyController::class, 'promotions'])->name('promotions');
    Route::post('affiliates', [McpProxyController::class, 'affiliates'])->name('affiliates');
    Route::post('referrals', [McpProxyController::class, 'referrals'])->name('referrals');
    Route::post('testimonials', [McpProxyController::class, 'testimonials'])->name('testimonials');
    Route::post('reviews', [McpProxyController::class, 'reviews'])->name('reviews');
    Route::post('ratings', [McpProxyController::class, 'ratings'])->name('ratings');
    Route::post('bookmarks', [McpProxyController::class, 'bookmarks'])->name('bookmarks');
    Route::post('favorites', [McpProxyController::class, 'favorites'])->name('favorites');
    Route::post('watchlists', [McpProxyController::class, 'watchlists'])->name('watchlists');
    Route::post('playlists', [McpProxyController::class, 'playlists'])->name('playlists');
    Route::post('collections', [McpProxyController::class, 'collections'])->name('collections');
    Route::post('libraries', [McpProxyController::class, 'libraries'])->name('libraries');
    Route::post('archives', [McpProxyController::class, 'archives'])->name('archives');
    Route::post('repositories', [McpProxyController::class, 'repositories'])->name('repositories');
    Route::post('storages', [McpProxyController::class, 'storages'])->name('storages');
    Route::post('uploads', [McpProxyController::class, 'uploads'])->name('uploads');
    Route::post('downloads', [McpProxyController::class, 'downloads'])->name('downloads');
    Route::post('exports', [McpProxyController::class, 'exports'])->name('exports');
    Route::post('imports', [McpProxyController::class, 'imports'])->name('imports');
    Route::post('saves', [McpProxyController::class, 'saves'])->name('saves');
    Route::post('loads', [McpProxyController::class, 'loads'])->name('loads');
    Route::post('backs', [McpProxyController::class, 'backs'])->name('backs');
    Route::post('forwards', [McpProxyController::class, 'forwards'])->name('forwards');
    Route::post('ups', [McpProxyController::class, 'ups'])->name('ups');
    Route::post('downs', [McpProxyController::class, 'downs'])->name('downs');
    Route::post('lefts', [McpProxyController::class, 'lefts'])->name('lefts');
    Route::post('rights', [McpProxyController::class, 'rights'])->name('rights');
    Route::post('zooms', [McpProxyController::class, 'zooms'])->name('zooms');
    Route::post('rotates', [McpProxyController::class, 'rotates'])->name('rotates');
    Route::post('flips', [McpProxyController::class, 'flips'])->name('flips');
    Route::post('crops', [McpProxyController::class, 'crops'])->name('crops');
    Route::post('pauses', [McpProxyController::class, 'pauses'])->name('pauses');
    Route::post('resumes', [McpProxyController::class, 'resumes'])->name('resumes');
    Route::post('stops', [McpProxyController::class, 'stops'])->name('stops');
    Route::post('starts', [McpProxyController::class, 'starts'])->name('starts');
    Route::post('restarts', [McpProxyController::class, 'restarts'])->name('restarts');
    Route::post('deletes', [McpProxyController::class, 'deletes'])->name('deletes');
    Route::post('creates', [McpProxyController::class, 'creates'])->name('creates');
    Route::post('imports-exports', [McpProxyController::class, 'importsExports'])->name('imports-exports');
    Route::post('exports-imports', [McpProxyController::class, 'exportsImports'])->name('exports-imports');
    Route::post('saves-loads', [McpProxyController::class, 'savesLoads'])->name('saves-loads');
    Route::post('backs-forwards', [McpProxyController::class, 'backsForwards'])->name('backs-forwards');
    Route::post('ups-downs', [McpProxyController::class, 'upsDowns'])->name('ups-downs');
    Route::post('lefts-rights', [McpProxyController::class, 'leftsRights'])->name('lefts-rights');
    Route::post('zooms-rotates', [McpProxyController::class, 'zoomsRotates'])->name('zooms-rotates');
    Route::post('flips-crops', [McpProxyController::class, 'flipsCrops'])->name('flips-crops');
    Route::post('pauses-resumes', [McpProxyController::class, 'pausesResumes'])->name('pauses-resumes');
    Route::post('stops-starts', [McpProxyController::class, 'stopsStarts'])->name('stops-starts');
    Route::post('restarts-deletes', [McpProxyController::class, 'restartsDeletes'])->name('restarts-deletes');
    Route::post('creates-imports', [McpProxyController::class, 'createsImports'])->name('creates-imports');
    Route::post('exports-deletes', [McpProxyController::class, 'exportsDeletes'])->name('exports-deletes');
    Route::post('imports-deletes', [McpProxyController::class, 'importsDeletes'])->name('imports-deletes');
    Route::post('saves-deletes', [McpProxyController::class, 'savesDeletes'])->name('saves-deletes');
    Route::post('backs-deletes', [McpProxyController::class, 'backsDeletes'])->name('backs-deletes');
    Route::post('forwards-deletes', [McpProxyController::class, 'forwardsDeletes'])->name('forwards-deletes');
    Route::post('ups-deletes', [McpProxyController::class, 'upsDeletes'])->name('ups-deletes');
    Route::post('downs-deletes', [McpProxyController::class, 'downsDeletes'])->name('downs-deletes');
    Route::post('lefts-deletes', [McpProxyController::class, 'leftsDeletes'])->name('lefts-deletes');
    Route::post('rights-deletes', [McpProxyController::class, 'rightsDeletes'])->name('rights-deletes');
    Route::post('zooms-deletes', [McpProxyController::class, 'zoomsDeletes'])->name('zooms-deletes');
    Route::post('rotates-deletes', [McpProxyController::class, 'rotatesDeletes'])->name('rotates-deletes');
    Route::post('flips-deletes', [McpProxyController::class, 'flipsDeletes'])->name('flips-deletes');
    Route::post('crops-deletes', [McpProxyController::class, 'cropsDeletes'])->name('crops-deletes');
    Route::post('pauses-deletes', [McpProxyController::class, 'pausesDeletes'])->name('pauses-deletes');
    Route::post('resumes-deletes', [McpProxyController::class, 'resumesDeletes'])->name('resumes-deletes');
    Route::post('stops-deletes', [McpProxyController::class, 'stopsDeletes'])->name('stops-deletes');
    Route::post('starts-deletes', [McpProxyController::class, 'startsDeletes'])->name('starts-deletes');
    Route::post('restarts-deletes', [McpProxyController::class, 'restartsDeletes'])->name('restarts-deletes');
    Route::post('deletes-deletes', [McpProxyController::class, 'deletesDeletes'])->name('deletes-deletes');
    Route::post('creates-creates', [McpProxyController::class, 'createsCreates'])->name('creates-creates');
    Route::post('imports-imports', [McpProxyController::class, 'importsImports'])->name('imports-imports');
    Route::post('exports-exports', [McpProxyController::class, 'exportsExports'])->name('exports-exports');
    Route::post('saves-saves', [McpProxyController::class, 'savesSaves'])->name('saves-saves');
    Route::post('backs-backs', [McpProxyController::class, 'backsBacks'])->name('backs-backs');
    Route::post('forwards-forwards', [McpProxyController::class, 'forwardsForwards'])->name('forwards-forwards');
    Route::post('ups-ups', [McpProxyController::class, 'upsUps'])->name('ups-ups');
    Route::post('downs-downs', [McpProxyController::class, 'downsDowns'])->name('downs-downs');
    Route::post('lefts-lefts', [McpProxyController::class, 'leftsLefts'])->name('lefts-lefts');
    Route::post('rights-rights', [McpProxyController::class, 'rightsRights'])->name('rights-rights');
    Route::post('zooms-zooms', [McpProxyController::class, 'zoomsZooms'])->name('zooms-zooms');
    Route::post('rotates-rotates', [McpProxyController::class, 'rotatesRotates'])->name('rotates-rotates');
    Route::post('flips-flips', [McpProxyController::class, 'flipsFlips'])->name('flips-flips');
    Route::post('crops-crops', [McpProxyController::class, 'cropsCrops'])->name('crops-crops');
    Route::post('pauses-pauses', [McpProxyController::class, 'pausesPauses'])->name('pauses-pauses');
    Route::post('resumes-resumes', [McpProxyController::class, 'resumesResumes'])->name('resumes-resumes');
    Route::post('stops-stops', [McpProxyController::class, 'stopsStops'])->name('stops-stops');
    Route::post('starts-starts', [McpProxyController::class, 'startsStarts'])->name('starts-starts');
    Route::post('restarts-restarts', [McpProxyController::class, 'restartsRestarts'])->name('restarts-restarts');
});

// Routes API pour les contacts externes
Route::prefix('external-contacts')->name('api.external-contacts.')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ExternalContactController::class, 'apiIndex'])->name('index');
    Route::get('/search', [ExternalContactController::class, 'apiSearch'])->name('search');
    Route::get('/{id}', [ExternalContactController::class, 'apiShow'])->name('show');
});

// Routes API pour les organisations externes
Route::prefix('external-organizations')->name('api.external-organizations.')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ExternalOrganizationController::class, 'apiIndex'])->name('index');
    Route::get('/search', [ExternalOrganizationController::class, 'apiSearch'])->name('search');
    Route::get('/{id}', [ExternalOrganizationController::class, 'apiShow'])->name('show');
});

// Routes API pour la gestion de la configuration JSON des templates de workflow
Route::prefix('workflows/templates')->name('api.workflows.templates.')->middleware('auth:sanctum')->group(function () {
    // Configuration complète
    Route::get('{template}/configuration', [App\Http\Controllers\WorkflowTemplateController::class, 'getConfiguration'])->name('configuration.show');
    Route::put('{template}/configuration', [App\Http\Controllers\WorkflowTemplateController::class, 'updateConfiguration'])->name('configuration.update');
    Route::post('{template}/configuration/validate', [App\Http\Controllers\WorkflowTemplateController::class, 'validateConfiguration'])->name('configuration.validate');

    // Gestion des étapes individuelles
    Route::post('{template}/configuration/steps', [App\Http\Controllers\WorkflowTemplateController::class, 'addConfigurationStep'])->name('configuration.steps.store');
    Route::put('{template}/configuration/steps/{stepId}', [App\Http\Controllers\WorkflowTemplateController::class, 'updateConfigurationStep'])->name('configuration.steps.update');
    Route::delete('{template}/configuration/steps/{stepId}', [App\Http\Controllers\WorkflowTemplateController::class, 'deleteConfigurationStep'])->name('configuration.steps.destroy');

    // Réorganisation des étapes
    Route::put('{template}/configuration/reorder', [App\Http\Controllers\WorkflowTemplateController::class, 'reorderConfigurationSteps'])->name('configuration.reorder');
});
