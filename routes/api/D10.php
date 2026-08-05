<?php

use App\Http\Controllers\Api\V1\SearchCommunicationController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SearchDollyController;
use App\Http\Controllers\Api\V1\SearchMailController;
use App\Http\Controllers\Api\V1\SearchRecordController;
use App\Http\Controllers\Api\V1\SearchReservationController;
use App\Http\Controllers\Api\V1\SearchSlipController;

// Routes API v1 du domaine D10 — Recherche (porté le 2026-08-05).
// Domaine d'ACTIONS GET (pas de CRUD) : chaque endpoint exécute une recherche du
// back-office Blade et renvoie l'enveloppe paginée CONVENTIONS §2, ou `{ "data": [...] }`
// pour les sélecteurs/autocomplétions. Inclus dans le groupe api.v1. (auth:sanctum +
// rate.limit) de routes/api.php.

Route::prefix('search')->name('search.')->group(function () {
    // Recherche libre unifiée (SearchController Blade) — org-scopée (R03).
    Route::get('records', [SearchController::class, 'records'])->name('records');
    Route::get('mails', [SearchController::class, 'mails'])->name('mails');
    Route::get('slips', [SearchController::class, 'slips'])->name('slips');

    // Recherche avancée, tri et sélecteurs de notices (SearchRecordController Blade).
    Route::get('records/advanced', [SearchRecordController::class, 'advanced'])->name('records.advanced');
    Route::get('records/sort', [SearchRecordController::class, 'sort'])->name('records.sort');
    Route::get('records/last', [SearchRecordController::class, 'last'])->name('records.last');
    Route::get('records/words', [SearchRecordController::class, 'words'])->name('records.words');
    Route::get('records/activities', [SearchRecordController::class, 'activities'])->name('records.activities');

    // Sélecteurs de localisation (arborescence bâtiment → conteneur).
    Route::get('locations/buildings', [SearchRecordController::class, 'buildings'])->name('locations.buildings');
    Route::get('locations/floors', [SearchRecordController::class, 'floors'])->name('locations.floors');
    Route::get('locations/rooms', [SearchRecordController::class, 'rooms'])->name('locations.rooms');
    Route::get('locations/shelves', [SearchRecordController::class, 'shelves'])->name('locations.shelves');
    Route::get('locations/containers', [SearchRecordController::class, 'containers'])->name('locations.containers');

    // Courriers (SearchMailController + SearchMailFeedbackController Blade).
    Route::get('mails/advanced', [SearchMailController::class, 'advanced'])->name('mails.advanced');
    Route::get('mails/typologies', [SearchMailController::class, 'typologies'])->name('mails.typologies');
    Route::get('mails/feedback', [SearchMailController::class, 'feedback'])->name('mails.feedback');

    // Communications & réservations.
    Route::get('communications', [SearchCommunicationController::class, 'index'])->name('communications');
    Route::get('communications/advanced', [SearchCommunicationController::class, 'advanced'])->name('communications.advanced');
    Route::get('reservations', [SearchReservationController::class, 'index'])->name('reservations');

    // Bordereaux (SearchSlipController Blade).
    Route::get('slips/sort', [SearchSlipController::class, 'index'])->name('slips.sort');
    Route::get('slips/advanced', [SearchSlipController::class, 'advanced'])->name('slips.advanced');
    Route::get('slips/organisation', [SearchSlipController::class, 'organisation'])->name('slips.organisation');

    // Chariots (SearchdollyController Blade).
    Route::get('dollies', [SearchDollyController::class, 'index'])->name('dollies');
});
