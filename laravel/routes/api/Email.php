<?php

// Routes API v1 — Email (boîte de messagerie IMAP/SMTP, distincte du courrier
// administratif D06). Inclus dans le groupe api.v1. (auth:sanctum + rate.limit)
// de routes/api.php.

use App\Http\Controllers\Api\V1\EmailAccountController;
use App\Http\Controllers\Api\V1\EmailMessageController;
use App\Http\Controllers\Api\V1\EmailTagController;

// Gestion des comptes — reste accessible même module désactivé : c'est ici que
// l'admin l'active (POST .../toggle).
Route::apiResource('email-accounts', EmailAccountController::class);
Route::post('email-accounts/{emailAccount}/sync', [EmailAccountController::class, 'sync'])->name('email-accounts.sync');
Route::post('email-accounts/{emailAccount}/toggle-active', [EmailAccountController::class, 'toggleActive'])->name('email-accounts.toggle-active');
Route::get('email', [EmailAccountController::class, 'moduleStatus'])->name('email.status');
Route::post('email/toggle', [EmailAccountController::class, 'toggleModule'])->name('email.toggle');

// Consultation (boîte mail) — verrouillée par le module.
Route::middleware(['email.enabled'])->group(function () {
    Route::post('email-messages/send', [EmailMessageController::class, 'send'])->name('email-messages.send');
    Route::post('email-messages/{emailMessage}/tags', [EmailMessageController::class, 'attachTag'])->name('email-messages.tags.attach');
    Route::delete('email-messages/{emailMessage}/tags/{tagId}', [EmailMessageController::class, 'detachTag'])->name('email-messages.tags.detach');
    Route::apiResource('email-messages', EmailMessageController::class)->except(['store']);

    Route::apiResource('email-tags', EmailTagController::class)->except(['show']);
});
