<?php

// Routes API v1 du domaine D06 — Courrier (porté le 2026-08-04).
// Ce fichier est inclus dans le groupe `api.v1.` (auth:sanctum + rate.limit) de routes/api.php.
//
// Actions métier complexes (workflows courrier multi-étapes) NON exposées — TODO documenté
// en tête des contrôleurs concernés (voir MailController, BatchController, BatchTransactionController,
// MailArchiveController, MailAttachmentController, MailContainerController) :
//   - téléversement de pièces jointes                                      → POST /mail-attachments = 501
//   - réception/envoi de parapheur (table mail_transactions absente)       → POST /batch-transactions = 501
//
// La création de courrier (POST /mails) est portée depuis le 2026-08-05, sans téléversement
// (génération du code séquentiel, relations expéditeur/destinataire) — voir MailController.
//
// Règles d'ordre : les routes statiques (`count-unread`, `list`, `properties`) précèdent
// toujours les apiResource pour ne pas être capturées par `{mail}` / `{mail_container}`.

use App\Http\Controllers\Api\V1\BatchController;
use App\Http\Controllers\Api\V1\BatchTransactionController;
use App\Http\Controllers\Api\V1\MailActionController;
use App\Http\Controllers\Api\V1\MailArchiveController;
use App\Http\Controllers\Api\V1\MailAttachmentController;
use App\Http\Controllers\Api\V1\MailContainerController;
use App\Http\Controllers\Api\V1\MailController;
use App\Http\Controllers\Api\V1\MailPriorityController;
use App\Http\Controllers\Api\V1\MailTypologyController;

// --- Référentiels globaux (motif D01) ---------------------------------------
Route::apiResource('mail-actions', MailActionController::class)->except(['create', 'edit']);
Route::apiResource('mail-priorities', MailPriorityController::class)->except(['create', 'edit']);
Route::apiResource('mail-typologies', MailTypologyController::class)->except(['create', 'edit']);

// --- Courriers (org-scopés, R03) --------------------------------------------
Route::get('mails/count-unread', [MailController::class, 'countUnread'])->name('mails.count-unread');
Route::apiResource('mails', MailController::class)->except(['create', 'edit']);

// --- Parapheurs / transactions (org-scopés, R03) ----------------------------
Route::apiResource('batches', BatchController::class)->except(['create', 'edit']);
Route::apiResource('batch-transactions', BatchTransactionController::class)->except(['create', 'edit']);

// --- Contenants de courrier (org-scopés, R03) -------------------------------
Route::get('mail-containers/list', [MailContainerController::class, 'getContainers'])->name('mail-containers.list');
Route::get('mail-containers/properties', [MailContainerController::class, 'getContainerProperties'])->name('mail-containers.properties');
Route::apiResource('mail-containers', MailContainerController::class)->except(['create', 'edit']);

// --- Archives de courrier (org-scopées via le contenant, R03) ---------------
Route::apiResource('mail-archives', MailArchiveController::class)->except(['create', 'edit']);

// --- Pièces jointes (ressource plate, filtre `?filter[mail_id]=`, R03) ------
Route::apiResource('mail-attachments', MailAttachmentController::class)
    ->only(['index', 'show', 'store', 'destroy']);
