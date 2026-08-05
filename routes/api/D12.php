<?php

// Routes API v1 du domaine D12 — Collaboration (porté le 2026-08-04).
// Ce fichier est inclus dans le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.

use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\WorkplaceActivityController;
use App\Http\Controllers\Api\V1\WorkplaceBookmarkController;
use App\Http\Controllers\Api\V1\WorkplaceConversationController;
use App\Http\Controllers\Api\V1\WorkplaceController;
use App\Http\Controllers\Api\V1\WorkplaceDocumentController;
use App\Http\Controllers\Api\V1\WorkplaceFolderController;
use App\Http\Controllers\Api\V1\WorkplaceMemberController;
use App\Http\Controllers\Api\V1\WorkplaceTemplateController;

// Actions métier déclarées AVANT les apiResource : `archive`/`settings` ne
// doivent pas être capturées par le paramètre `{workplace}`.
Route::post('workplaces/{workplace}/archive', [WorkplaceController::class, 'archive'])->name('workplaces.archive');
Route::get('workplaces/{workplace}/settings', [WorkplaceController::class, 'settings'])->name('workplaces.settings');

Route::apiResource('workplaces', WorkplaceController::class)->except(['create', 'edit']);

// Contenu partagé (WorkplaceContentController).
Route::get('workplaces/{workplace}/content/documents', [WorkplaceDocumentController::class, 'documents'])->name('workplaces.content.documents');
Route::post('workplaces/{workplace}/content/documents', [WorkplaceDocumentController::class, 'shareDocument'])->name('workplaces.content.share-document');
Route::delete('workplaces/{workplace}/content/documents/{document}', [WorkplaceDocumentController::class, 'unshareDocument'])->name('workplaces.content.unshare-document');
Route::post('workplaces/{workplace}/content/documents/{document}/feature', [WorkplaceDocumentController::class, 'featureDocument'])->name('workplaces.content.feature-document');
Route::get('workplaces/{workplace}/content/folders', [WorkplaceFolderController::class, 'folders'])->name('workplaces.content.folders');
Route::post('workplaces/{workplace}/content/folders', [WorkplaceFolderController::class, 'shareFolder'])->name('workplaces.content.share-folder');
Route::delete('workplaces/{workplace}/content/folders/{folder}', [WorkplaceFolderController::class, 'unshareFolder'])->name('workplaces.content.unshare-folder');
Route::post('workplaces/{workplace}/content/folders/{folder}/pin', [WorkplaceFolderController::class, 'pinFolder'])->name('workplaces.content.pin-folder');

// Activité (lecture seule).
Route::get('workplaces/{workplace}/activities', [WorkplaceActivityController::class, 'index'])->name('workplaces.activities.index');

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

// Conversations / chats (ChatController + WorkplaceMessageController fusionnés) :
// `messages` est déclaré AVANT l'apiResource pour ne pas être capturé.
Route::post('workplace-conversations/{workplace_conversation}/messages', [WorkplaceConversationController::class, 'storeMessage'])->name('workplace-conversations.messages.store');
Route::apiResource('workplace-conversations', WorkplaceConversationController::class)->except(['create', 'edit']);

Route::apiResource('workplace-templates', WorkplaceTemplateController::class)->except(['create', 'edit']);
Route::apiResource('tasks', TaskController::class)->except(['create', 'edit']);

// TODO D12 — actions non portées (étape 1.x) :
//  - WorkplaceInvitationController::accept (GET /workplaces/invitations/{token}) :
//    flux web (redirections login/register), à exposer côté API en phase 2.
//  - WorkplaceContentController::viewDocument (redirection vers records.show).
//  - WorkplaceContentController::searchFolders / searchDocuments (recherche AJAX
//    transverse sur les modèles D02 RecordDigitalFolder/Document).
//  - WorkplaceMessageController::renderChat (vues HTML), sans équivalent API.
