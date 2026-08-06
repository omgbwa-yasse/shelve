<?php

use App\Http\Controllers\Api\V1\RecordStatusController;
use App\Http\Controllers\Api\V1\RecordSupportController;
use App\Http\Controllers\Api\V1\RecordTypeController;
use App\Http\Controllers\Api\V1\RecordLevelController;
use App\Http\Controllers\Api\V1\RecordConfidentialityController;
use App\Http\Controllers\Api\V1\MetadataDefinitionController;
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
Route::get('record-types/{recordType}/metadata-fields', [RecordTypeController::class, 'metadataFields'])->name('record-types.metadata-fields');
Route::apiResource('record-types', RecordTypeController::class)->except(['create', 'edit']);
Route::get('record-levels', [RecordLevelController::class, 'index'])->name('record-levels.index');
Route::get('record-confidentialities', [RecordConfidentialityController::class, 'index'])->name('record-confidentialities.index');
Route::apiResource('metadata-definitions', MetadataDefinitionController::class)->except(['create', 'edit']);
// record-digital-folder-metadata-profiles / record-digital-document-metadata-profiles
// supprimées le 2026-08-06 avec RecordDigitalFolder/RecordDigitalDocument.

// Actions métier déclarées AVANT les apiResource : `status`, `reactivations`, `children`,
// `authors`, `containers` et `attachments` ne doivent pas être capturées par `{record}`.
// `records-trash` avant `records/{record}` pour la même raison.
Route::get('records-trash', [RecordController::class, 'trash'])->name('records.trash');
Route::get('records/{record}/metadata-fields', [RecordController::class, 'metadataFields'])->name('records.metadata-fields');
Route::patch('records/{record}/status', [RecordController::class, 'status'])->name('records.status');
Route::post('records/{record}/reactivations', [RecordController::class, 'reactivation'])->name('records.reactivations.store');
Route::post('record-reactivations/{reactivation}/approve', [RecordReactivationController::class, 'approve'])->name('record-reactivations.approve');
Route::post('record-reactivations/{reactivation}/reject', [RecordReactivationController::class, 'reject'])->name('record-reactivations.reject');

// `withTrashed()` : le binding implicite doit résoudre une notice déjà soft-supprimée.
Route::post('records/{record}/restore', [RecordController::class, 'restore'])->name('records.restore')->withTrashed();
Route::delete('records/{record}/force', [RecordController::class, 'forceDelete'])->name('records.force-delete')->withTrashed();

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
