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

// Public API routes with rate limiting
Route::prefix('public')->name('api.public.')->middleware('rate.limit:api_general,100,60')->group(function () {
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
        ->middleware('rate.limit:auth,3,60'); // 3 inscriptions par heure

    Route::post('users/verify-token', [PublicUserApiController::class, 'verifyToken'])
        ->name('users.verify-token')
        ->middleware('rate.limit:auth,10,60'); // 10 vérifications par heure

    Route::post('users/forgot-password', [PublicUserApiController::class, 'forgotPassword'])
        ->name('users.forgot-password')
        ->middleware('rate.limit:auth,3,60'); // 3 demandes de reset par heure

    Route::post('users/reset-password', [PublicUserApiController::class, 'resetPassword'])
        ->name('users.reset-password')
        ->middleware('rate.limit:auth,3,60'); // 3 resets par heure
});

// Secure public API routes with rate limiting
Route::prefix('public')->name('api.secure.public.')->middleware(['auth:sanctum', 'rate.limit:api_general,200,60'])->group(function () {
    Route::apiResource('users', PublicUserApiController::class)->names('users');
    Route::apiResource('document-requests', PublicDocumentRequestApiController::class)
        ->names("document-requests")
        ->middleware('rate.limit:document_request,20,60'); // 20 demandes de documents par heure
    Route::apiResource('responses', PublicResponseApiController::class)->names("responses");
});

// MCP Proxy routes - Communication avec le serveur MCP
Route::prefix('mcp')->name('mcp.')->middleware(['auth:web', 'rate.limit:api_general,100,60'])->group(function () {
    // Reformulation d'enregistrements d'archives
    Route::post('reformulate-record', [App\Http\Controllers\McpProxyController::class, 'reformulateRecord'])
        ->name('reformulate-record')
        ->middleware('rate.limit:mcp_reformulate,30,60'); // 30 reformulations par heure

    // Statut et information du serveur MCP
    Route::get('status', [App\Http\Controllers\McpProxyController::class, 'checkMcpStatus'])
        ->name('status');

    Route::get('info', [App\Http\Controllers\McpProxyController::class, 'getMcpInfo'])
        ->name('info');
});
