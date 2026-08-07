<?php

// Routes API v1 du domaine D11 (Dolly) — portage phase 1, ajoutées le 2026-08-04.
// Inclus dans le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.

use App\Http\Controllers\Api\V1\DollyActionController;
use App\Http\Controllers\Api\V1\DollyController;
use App\Http\Controllers\Api\V1\DollyHandlerController;
use Illuminate\Support\Facades\Route;

// Actions GET/POST déclarées AVANT l'apiResource : `action`, `list` et `store` ne
// doivent pas être capturés par le paramètre `{dolly}`.
Route::get('dollies/action', [DollyActionController::class, 'index'])->name('dollies.action');
Route::get('dollies/list', [DollyController::class, 'apiList'])->name('dollies.list');
Route::post('dollies/store', [DollyController::class, 'apiCreate'])->name('dollies.api-store');

Route::apiResource('dollies', DollyController::class)->except(['create', 'edit']);

// Ajouts / retraits d'éléments dans un chariot.
Route::post('dollies/{dolly}/add-record', [DollyController::class, 'addRecord'])->name('dollies.add-record');
Route::post('dollies/{dolly}/add-mail', [DollyController::class, 'addMail'])->name('dollies.add-mail');
Route::post('dollies/{dolly}/add-communication', [DollyController::class, 'addCommunication'])->name('dollies.add-communication');
Route::post('dollies/{dolly}/add-room', [DollyController::class, 'addRoom'])->name('dollies.add-room');
Route::post('dollies/{dolly}/add-container', [DollyController::class, 'addContainer'])->name('dollies.add-container');
Route::post('dollies/{dolly}/add-shelve', [DollyController::class, 'addShelve'])->name('dollies.add-shelve');
Route::post('dollies/{dolly}/add-slip-record', [DollyController::class, 'addSlipRecord'])->name('dollies.add-slip-record');
Route::post('dollies/{dolly}/add-digital-folder', [DollyController::class, 'addDigitalFolder'])->name('dollies.add-digital-folder');
Route::post('dollies/{dolly}/add-digital-document', [DollyController::class, 'addDigitalDocument'])->name('dollies.add-digital-document');

Route::post('dollies/{dolly}/add-slip', [DollyController::class, 'addSlip'])->name('dollies.add-slip');

// Actions utilitaires simples (2026-08-05) : renommage et vidage du chariot.
Route::post('dollies/{dolly}/rename', [DollyController::class, 'rename'])->name('dollies.rename');
Route::post('dollies/{dolly}/clear', [DollyController::class, 'clear'])->name('dollies.clear');

Route::delete('dollies/{dolly}/remove-record/{record}', [DollyController::class, 'removeRecord'])->name('dollies.remove-record');
Route::delete('dollies/{dolly}/remove-mail/{mail}', [DollyController::class, 'removeMail'])->name('dollies.remove-mail');
Route::delete('dollies/{dolly}/remove-communication/{communication}', [DollyController::class, 'removeCommunication'])->name('dollies.remove-communication');
Route::delete('dollies/{dolly}/remove-room/{room}', [DollyController::class, 'removeRoom'])->name('dollies.remove-room');
Route::delete('dollies/{dolly}/remove-container/{container}', [DollyController::class, 'removeContainer'])->name('dollies.remove-container');
Route::delete('dollies/{dolly}/remove-shelve/{shelve}', [DollyController::class, 'removeShelve'])->name('dollies.remove-shelve');
Route::delete('dollies/{dolly}/remove-slip-record/{slipRecord}', [DollyController::class, 'removeSlipRecord'])->name('dollies.remove-slip-record');
Route::delete('dollies/{dolly}/remove-slip/{slip}', [DollyController::class, 'removeSlip'])->name('dollies.remove-slip');
Route::delete('dollies/{dolly}/remove-digital-folder/{folder}', [DollyController::class, 'removeDigitalFolder'])->name('dollies.remove-digital-folder');
Route::delete('dollies/{dolly}/remove-digital-document/{document}', [DollyController::class, 'removeDigitalDocument'])->name('dollies.remove-digital-document');

// Handler JSON (`dolly-handler/*`) — formes de réponse conservées telles quelles.
Route::get('dolly-handler/list', [DollyHandlerController::class, 'list'])->name('dolly-handler.list');
Route::post('dolly-handler/create', [DollyHandlerController::class, 'addDolly'])->name('dolly-handler.create');
Route::post('dolly-handler/add-items', [DollyHandlerController::class, 'addItems'])->name('dolly-handler.add-items');
Route::delete('dolly-handler/remove-items', [DollyHandlerController::class, 'removeItems'])->name('dolly-handler.remove-items');
Route::delete('dolly-handler/clean', [DollyHandlerController::class, 'clean'])->name('dolly-handler.clean');
Route::delete('dolly-handler/{dolly_id}', [DollyHandlerController::class, 'deleteDolly'])->name('dolly-handler.delete');
