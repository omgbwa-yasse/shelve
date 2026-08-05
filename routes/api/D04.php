<?php

use App\Http\Controllers\Api\V1\SlipController;
use App\Http\Controllers\Api\V1\SlipRecordAttachmentController;
use App\Http\Controllers\Api\V1\SlipRecordContainerController;
use App\Http\Controllers\Api\V1\SlipRecordController;
use App\Http\Controllers\Api\V1\SlipStatusController;

// Actions métier déclarées AVANT les apiResource : `receive`, `approve` et `upload`
// ne doivent pas être capturées par les paramètres `{slip}` / `{record}`.
Route::post('slips/{slip}/receive', [SlipController::class, 'receive'])->name('slips.receive');
Route::post('slips/{slip}/approve', [SlipController::class, 'approve'])->name('slips.approve');

Route::apiResource('slips', SlipController::class)->except(['create', 'edit']);
Route::apiResource('slip-statuses', SlipStatusController::class)->except(['create', 'edit']);

// Ressources imbriquées sous un bordereau (org-scopées par héritage, R03).
Route::apiResource('slips/{slip}/records', SlipRecordController::class)
    ->parameters(['records' => 'slipRecord'])
    ->names('slips.records')
    ->except(['create', 'edit']);

Route::apiResource('slips/{slip}/records/{record}/containers', SlipRecordContainerController::class)
    ->parameters(['containers' => 'container'])
    ->names('slips.records.containers')
    ->except(['create', 'edit']);

Route::get('slips/{slip}/records/{record}/attachments', [SlipRecordAttachmentController::class, 'index'])
    ->name('slips.records.attachments.index');
Route::post('slips/{slip}/records/{record}/attachments/upload', [SlipRecordAttachmentController::class, 'upload'])
    ->name('slips.records.attachments.upload');
Route::delete('slips/{slip}/records/{record}/attachments/{attachment}', [SlipRecordAttachmentController::class, 'destroy'])
    ->name('slips.records.attachments.destroy');

// Les routes `transferrings/containers` (SlipContainerController) portent le même modèle
// `Container`, déjà exposé par la ressource D03 `containers` : elles sont fusionnées
// dans cette ressource (même motif que les `trolleys` du BuildingController en D03).
