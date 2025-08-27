<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Controllers\PromptController;

// Public API routes with rate limiting
Route::prefix('public')->name('api.public.')->middleware('rate.limit:api_general,100,60')->group(function () {
    $authRateLimit = 'rate.limit:auth,3,60';
    // Records - Services ouverts (rate limit plus restrictif)
    Route::apiResource('records', PublicRecordApiController::class)
        ->names('records')
        ->except(['create', 'edit'])
        ->middleware('rate.limit:search,50,60'); // 50 recherches par heure pour les records

    // Users (Authentication) - rate limit très restrictif
    Route::post('users/login', [PublicUserApiController::class, 'login'])
        ->name('users.login')
        ->middleware('rate.limit:auth,5,60'); // 5 tentatives de connexion par heure

    Route::post('users/register', [PublicUserApiController::class, 'register'])
        ->name('users.register')
        ->middleware($authRateLimit); // 3 inscriptions par heure

    Route::post('users/verify-token', [PublicUserApiController::class, 'verifyToken'])
        ->name('users.verify-token')
        ->middleware('rate.limit:auth,10,60'); // 10 vérifications par heure

    Route::post('users/forgot-password', [PublicUserApiController::class, 'forgotPassword'])
        ->name('users.forgot-password')
        ->middleware($authRateLimit); // 3 demandes de reset par heure

    Route::post('users/reset-password', [PublicUserApiController::class, 'resetPassword'])
        ->name('users.reset-password')
        ->middleware($authRateLimit); // 3 resets par heure
});



// Secure public API routes with rate limiting
Route::prefix('public')->name('api.secure.public.')->middleware(['auth:sanctum', 'rate.limit:api_general,200,60'])->group(function () {
    Route::apiResource('users', PublicUserApiController::class)->names('users');
    Route::apiResource('document-requests', PublicDocumentRequestApiController::class)
        ->names("document-requests")
        ->middleware('rate.limit:document_request,20,60'); // 20 demandes de documents par heure
    Route::apiResource('responses', PublicResponseApiController::class)->names("responses");
});



// MCP/AI API routes retirées

// Prompt & AI routes (use web session auth to support same-origin Blade pages)
// Add a specific rate limit for AI actions to prevent abuse
Route::middleware(['web', 'auth', 'rate.limit:ai,30,60'])->group(function () {
    Route::get('prompts', [PromptController::class, 'index'])->name('api.prompts.index');
    Route::get('prompts/{id}', [PromptController::class, 'show'])->name('api.prompts.show');
    Route::post('prompts/{id}/actions', [PromptController::class, 'actions'])->name('api.prompts.actions');
    // Apply AI suggestions to records
    Route::prefix('records/{record}/ai')->name('api.records.ai.')->group(function () {
        Route::post('title', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'saveTitle'])->name('title');
        Route::post('summary', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'saveSummary'])->name('summary');
        Route::post('thesaurus', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'saveThesaurus'])->name('thesaurus');
    Route::post('thesaurus/suggest', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'suggestThesaurus'])->name('thesaurus.suggest');
    Route::post('thesaurus/auto', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'autoSuggestThesaurus'])->name('thesaurus.auto');
    Route::post('thesaurus/suggest-json', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'suggestThesaurusFromJson'])->name('thesaurus.suggest_json');
    Route::post('activity', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'saveActivity'])->name('activity');
    Route::post('activity/suggest', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'suggestActivityCandidates'])->name('activity.suggest');
        Route::post('keywords', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'saveKeywords'])->name('keywords');
        Route::post('keywords/suggest', [\App\Http\Controllers\Api\AiRecordApplyController::class, 'suggestKeywords'])->name('keywords.suggest');
    });
});
