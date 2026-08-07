<?php

// Routes API v1 du domaine D12 — Collaboration (porté le 2026-08-04).
// Ce fichier est inclus dans le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.

use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\WorkplaceActivityController;
use App\Http\Controllers\Api\V1\WorkplaceBookmarkController;
use App\Http\Controllers\Api\V1\WorkplaceConversationController;
use App\Http\Controllers\Api\V1\WorkplaceController;
use App\Http\Controllers\Api\V1\WorkplaceDocumentController;
use App\Http\Controllers\Api\V1\WorkplaceMemberController;
use App\Http\Controllers\Api\V1\WorkplaceTemplateController;

// Actions métier déclarées AVANT les apiResource : `archive`/`settings` ne
// doivent pas être capturées par le paramètre `{workplace}`.
Route::post('workplaces/{workplace}/archive', [WorkplaceController::class, 'archive'])->name('workplaces.archive');
Route::get('workplaces/{workplace}/settings', [WorkplaceController::class, 'settings'])->name('workplaces.settings');

Route::apiResource('workplaces', WorkplaceController::class)->except(['create', 'edit']);

// Contenu partagé (WorkplaceContentController / WorkplaceFolder|DocumentController)
// supprimé le 2026-08-06 avec RecordDigitalFolder/RecordDigitalDocument, dont
// WorkplaceFolder/WorkplaceDocument dépendaient.

// Activité (lecture seule).
Route::get('workplaces/{workplace}/activities', [WorkplaceActivityController::class, 'index'])->name('workplaces.activities.index');

// Calendrier du workplace : projets/jalons/tâches datés.
Route::get('workplaces/{workplace}/calendar', [WorkplaceController::class, 'calendar'])->name('workplaces.calendar');

// Favoris (toggle).
Route::get('workplaces/{workplace}/bookmarks', [WorkplaceBookmarkController::class, 'index'])->name('workplaces.bookmarks.index');
Route::post('workplaces/{workplace}/bookmarks', [WorkplaceBookmarkController::class, 'store'])->name('workplaces.bookmarks.store');
Route::delete('workplaces/{workplace}/bookmarks/{bookmark}', [WorkplaceBookmarkController::class, 'destroy'])->name('workplaces.bookmarks.destroy');

// Membres + invitations + permissions/notifications.
Route::get('workplaces/{workplace}/members', [WorkplaceMemberController::class, 'index'])->name('workplaces.members.index');
Route::post('workplaces/{workplace}/members', [WorkplaceMemberController::class, 'store'])->name('workplaces.members.store');
Route::put('workplaces/{workplace}/members/{member}', [WorkplaceMemberController::class, 'update'])->name('workplaces.members.update');
Route::delete('workplaces/{workplace}/members/{member}', [WorkplaceMemberController::class, 'destroy'])->name('workplaces.members.destroy');
Route::put('workplaces/{workplace}/members/{member}/permissions', [WorkplaceMemberController::class, 'updatePermissions'])->name('workplaces.members.permissions');
Route::put('workplaces/{workplace}/members/{member}/notifications', [WorkplaceMemberController::class, 'updateNotifications'])->name('workplaces.members.notifications');

// Bibliothèque Documents de l'espace (dossiers + fichiers, basée sur `records`).
// `upload`/`download`/`share`/`unshare`/`transfer` déclarés AVANT le
// `destroy`/{record} pour ne pas être capturés.
Route::get('workplaces/{workplace}/documents', [WorkplaceDocumentController::class, 'index'])->name('workplaces.documents.index');
Route::post('workplaces/{workplace}/folders', [WorkplaceDocumentController::class, 'storeFolder'])->name('workplaces.documents.folders.store');
Route::post('workplaces/{workplace}/documents/upload', [WorkplaceDocumentController::class, 'upload'])->name('workplaces.documents.upload');
Route::get('workplaces/{workplace}/documents/{record}/download', [WorkplaceDocumentController::class, 'download'])->name('workplaces.documents.download');
Route::post('workplaces/{workplace}/documents/{record}/share', [WorkplaceDocumentController::class, 'share'])->name('workplaces.documents.share');
Route::post('workplaces/{workplace}/documents/{record}/unshare', [WorkplaceDocumentController::class, 'unshare'])->name('workplaces.documents.unshare');
Route::post('workplaces/{workplace}/documents/{record}/transfer', [WorkplaceDocumentController::class, 'transfer'])->name('workplaces.documents.transfer');
Route::delete('workplaces/{workplace}/documents/{record}', [WorkplaceDocumentController::class, 'destroy'])->name('workplaces.documents.destroy');

// Conversations / chats (ChatController + WorkplaceMessageController fusionnés) :
// `messages` est déclaré AVANT l'apiResource pour ne pas être capturé.
Route::post('workplace-conversations/{workplace_conversation}/messages', [WorkplaceConversationController::class, 'storeMessage'])->name('workplace-conversations.messages.store');
Route::apiResource('workplace-conversations', WorkplaceConversationController::class)->except(['create', 'edit']);

Route::apiResource('workplace-templates', WorkplaceTemplateController::class)->except(['create', 'edit']);
Route::apiResource('tasks', TaskController::class)->except(['create', 'edit']);

// TODO D12 — actions non portées (étape 1.x) :
//  - WorkplaceInvitationController::accept (GET /workplaces/invitations/{token}) :
//    flux web (redirections login/register), à exposer côté API en phase 2.
//  - WorkplaceMessageController::renderChat (vues HTML), sans équivalent API.
