<?php

// Routes API v1 du domaine D07 (Cycle de vie) — portage phase 1, ajoutées le 2026-08-04.
// Inclus dans le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.

use App\Http\Controllers\Api\V1\DeclassementListController;
use App\Http\Controllers\Api\V1\LifeCycleController;
use App\Http\Controllers\Api\V1\RetentionActivityController;
use App\Http\Controllers\Api\V1\RetentionController;
use App\Http\Controllers\Api\V1\RetentionLawArticleController;
use Illuminate\Support\Facades\Route;

// Actions métier déclarées AVANT l'apiResource : `eligible-records` ne doit pas être
// capturé par le paramètre `{declassementList}`.
Route::get('declassement-lists/eligible-records', [DeclassementListController::class, 'eligibleRecords'])->name('declassement-lists.eligible-records');
Route::apiResource('declassement-lists', DeclassementListController::class)->except(['create', 'edit']);
Route::post('declassement-lists/{declassementList}/records', [DeclassementListController::class, 'addRecords'])->name('declassement-lists.records.store');
Route::delete('declassement-lists/{declassementList}/records/{declassementRecord}', [DeclassementListController::class, 'removeRecord'])->name('declassement-lists.records.destroy');
Route::post('declassement-lists/{declassementList}/comments', [DeclassementListController::class, 'comment'])->name('declassement-lists.comments.store');
Route::post('declassement-lists/{declassementList}/request-approval', [DeclassementListController::class, 'requestApproval'])->name('declassement-lists.request-approval');
Route::post('declassement-lists/{declassementList}/approve', [DeclassementListController::class, 'approve'])->name('declassement-lists.approve');
Route::post('declassement-lists/{declassementList}/validate', [DeclassementListController::class, 'validateList'])->name('declassement-lists.validate');
Route::post('declassement-lists/{declassementList}/process', [DeclassementListController::class, 'process'])->name('declassement-lists.process');
Route::post('declassement-lists/{declassementList}/reject', [DeclassementListController::class, 'reject'])->name('declassement-lists.reject');

Route::apiResource('retentions', RetentionController::class)->except(['create', 'edit']);

// Pivots sans colonne `id` (clé composite) : pas d'apiResource — l'apiResource lierait
// `{retentionActivity}`/`{retentionLawArticle}` à une clé primaire inexistante.
Route::get('retention-activities', [RetentionActivityController::class, 'index'])->name('retention-activities.index');
Route::post('retention-activities', [RetentionActivityController::class, 'store'])->name('retention-activities.store');
Route::delete('retention-activities/{retention}/{activity}', [RetentionActivityController::class, 'destroy'])->name('retention-activities.destroy');

Route::get('retention-law-articles', [RetentionLawArticleController::class, 'index'])->name('retention-law-articles.index');
Route::post('retention-law-articles', [RetentionLawArticleController::class, 'store'])->name('retention-law-articles.store');
Route::patch('retention-law-articles/{retention}/{lawArticle}', [RetentionLawArticleController::class, 'update'])->name('retention-law-articles.update');
Route::delete('retention-law-articles/{retention}/{lawArticle}', [RetentionLawArticleController::class, 'destroy'])->name('retention-law-articles.destroy');

// Rapports de cycle de vie (lecture seule, domaines de la phase 3).
Route::get('transferrings/lifecycle/retain', [LifeCycleController::class, 'recordToRetain'])->name('lifecycle.retain');
Route::get('transferrings/lifecycle/keep', [LifeCycleController::class, 'recordToKeep'])->name('lifecycle.keep');
Route::get('transferrings/lifecycle/transfer', [LifeCycleController::class, 'recordToTransfer'])->name('lifecycle.transfer');
Route::get('transferrings/lifecycle/sort', [LifeCycleController::class, 'recordToSort'])->name('lifecycle.sort');
Route::get('transferrings/lifecycle/store', [LifeCycleController::class, 'recordToStore'])->name('lifecycle.store');
Route::get('transferrings/lifecycle/eliminate', [LifeCycleController::class, 'recordToEliminate'])->name('lifecycle.eliminate');
