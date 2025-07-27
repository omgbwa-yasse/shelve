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







Route::prefix('public')->name('api.public.')->group(function () {
    // Records - Services ouverts
    Route::apiResource('records', PublicRecordApiController::class)->names('records')->except(['create', 'edit']);
    Route::apiResource('records.attachments', PublicRecordApiController::class)->names('records.attachments')->except(['create', 'edit']);

    // Users (Authentication)
    Route::post('users/login', [PublicUserApiController::class, 'login'])->name('users.login');
    Route::post('users/register', [PublicUserApiController::class, 'register'])->name('users.register');
    Route::post('users/verify-token', [PublicUserApiController::class, 'verifyToken'])->name('users.verify-token');
    Route::post('users/forgot-password', [PublicUserApiController::class, 'forgotPassword'])->name('users.forgot-password');
    Route::post('users/reset-password', [PublicUserApiController::class, 'resetPassword'])->name('users.reset-password');

});





Route::prefix('public')->name('api.secure.public.')->middleware('auth:sanctum')->group(function () {

    Route::apiResource('users', PublicUserApiController::class)->names('users');
    Route::apiResource('document-requests', PublicDocumentRequestApiController::class)->names("document-requests");
    Route::apiResource('responses', PublicResponseApiController::class)->names("responses");
    Route::apiResource('responses.attachments', PublicResponseAttachmentApiController::class)->names("responses.attachments");


});







