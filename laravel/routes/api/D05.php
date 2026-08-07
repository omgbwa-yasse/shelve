<?php

use App\Http\Controllers\Api\V1\CommunicationController;
use App\Http\Controllers\Api\V1\CommunicationRecordController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\ReservationRecordController;

// Actions métier déclarées AVANT les apiResource (motif D01/D03) : les verbes
// `validate`, `reject`, `transmit`, `return-*` ne doivent pas être capturés par les
// paramètres `{communication}` / `{reservation}`.
Route::post('communications/{communication}/validate', [CommunicationController::class, 'validateCommunication'])->name('communications.validate');
Route::post('communications/{communication}/reject', [CommunicationController::class, 'reject'])->name('communications.reject');
Route::post('communications/{communication}/transmit', [CommunicationController::class, 'transmission'])->name('communications.transmission');
Route::post('communications/{communication}/return-effective', [CommunicationController::class, 'returnEffective'])->name('communications.return-effective');
Route::post('communications/{communication}/return-cancel', [CommunicationController::class, 'returnCancel'])->name('communications.return-cancel');

Route::apiResource('communications', CommunicationController::class)->except(['create', 'edit']);

Route::apiResource('communications/{communication}/records', CommunicationRecordController::class)
    ->parameters(['records' => 'communicationRecord'])
    ->names('communications.records')
    ->only(['index', 'show', 'store', 'update', 'destroy']);

Route::post('communications/{communication}/records/{communicationRecord}/return-effective', [CommunicationRecordController::class, 'returnEffective'])
    ->name('communications.records.return-effective');
Route::post('communications/{communication}/records/{communicationRecord}/return-cancel', [CommunicationRecordController::class, 'returnCancel'])
    ->name('communications.records.return-cancel');

Route::post('reservations/{reservation}/mark-returned', [ReservationController::class, 'markAsReturned'])->name('reservations.mark-returned');

Route::apiResource('reservations', ReservationController::class)->except(['create', 'edit']);

Route::apiResource('reservations/{reservation}/records', ReservationRecordController::class)
    ->parameters(['records' => 'reservationRecord'])
    ->names('reservations.records')
    ->only(['index', 'show', 'store', 'update', 'destroy']);

// `activities.communicabilities.*` (activityCommunicabilityController) : aucun modèle ni
// table dédiés — la fonctionnalité manipule `Activity.communicability_id`, déjà couvert
// par la ressource D01 `activities`. Non porté (voir JOURNAL du portage D05).
