<?php

use App\Http\Controllers\Api\V1\RecordStatusController;
use App\Http\Controllers\Api\V1\RecordSupportController;
use App\Http\Controllers\Api\V1\RecordTypeController;
use App\Http\Controllers\Api\V1\MetadataDefinitionController;
use App\Http\Controllers\Api\V1\RecordDigitalFolderMetadataProfileController;
use App\Http\Controllers\Api\V1\RecordDigitalDocumentMetadataProfileController;
use App\Http\Controllers\Api\V1\RecordController;
use App\Http\Controllers\Api\V1\RecordChildController;
use App\Http\Controllers\Api\V1\RecordAuthorController;
use App\Http\Controllers\Api\V1\RecordContainerController;
use App\Http\Controllers\Api\V1\RecordAttachmentController;
use App\Http\Controllers\Api\V1\RecordReactivationController;

// Routes API v1 du domaine D02 — sous-référentiels Records (porté le 2026-08-04),
// notices et pivots (portés le 2026-08-05).
// Ce fichier est inclus dans le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.

// Sous-référentiels globaux (déjà portés).
Route::apiResource('record-statuses', RecordStatusController::class)->except(['create', 'edit']);
Route::apiResource('record-supports', RecordSupportController::class)->except(['create', 'edit']);
Route::apiResource('record-types', RecordTypeController::class)->except(['create', 'edit']);
Route::apiResource('metadata-definitions', MetadataDefinitionController::class)->except(['create', 'edit']);
Route::apiResource('record-digital-folder-metadata-profiles', RecordDigitalFolderMetadataProfileController::class)
    ->parameters(['record-digital-folder-metadata-profiles' => 'folder_profile'])
    ->except(['create', 'edit']);
Route::apiResource('record-digital-document-metadata-profiles', RecordDigitalDocumentMetadataProfileController::class)
    ->parameters(['record-digital-document-metadata-profiles' => 'document_profile'])
    ->except(['create', 'edit']);

// Actions métier déclarées AVANT les apiResource : `status`, `reactivations`, `children`,
// `authors`, `containers` et `attachments` ne doivent pas être capturées par `{record}`.
Route::patch('records/{record}/status', [RecordController::class, 'status'])->name('records.status');
Route::post('records/{record}/reactivations', [RecordController::class, 'reactivation'])->name('records.reactivations.store');
Route::post('record-reactivations/{reactivation}/approve', [RecordReactivationController::class, 'approve'])->name('record-reactivations.approve');
Route::post('record-reactivations/{reactivation}/reject', [RecordReactivationController::class, 'reject'])->name('record-reactivations.reject');

Route::apiResource('records', RecordController::class)->except(['create', 'edit']);
Route::apiResource('record-reactivations', RecordReactivationController::class)->only(['index']);

// Ressources imbriquées sous une notice (org-scopées par héritage, motif D03).
Route::apiResource('records/{record}/children', RecordChildController::class)
    ->parameters(['children' => 'child'])
    ->names('records.children')
    ->except(['create', 'edit']);

Route::apiResource('records/{record}/authors', RecordAuthorController::class)
    ->parameters(['authors' => 'author'])
    ->names('records.authors')
    ->except(['create', 'edit', 'update']);

Route::apiResource('records/{record}/containers', RecordContainerController::class)
    ->parameters(['containers' => 'container'])
    ->names('records.containers')
    ->except(['create', 'edit']);

Route::get('records/{record}/attachments', [RecordAttachmentController::class, 'index'])
    ->name('records.attachments.index');
Route::post('records/{record}/attachments/upload', [RecordAttachmentController::class, 'upload'])
    ->name('records.attachments.upload');
Route::delete('records/{record}/attachments/{attachment}', [RecordAttachmentController::class, 'destroy'])
    ->name('records.attachments.destroy');
