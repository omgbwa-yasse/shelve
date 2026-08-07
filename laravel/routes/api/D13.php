<?php

// Routes API v1 du domaine D13 (Workflow) — portage phase 1, ajoutées le 2026-08-04.
// Inclus dans le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.

use App\Http\Controllers\Api\V1\WorkflowDefinitionController;
use App\Http\Controllers\Api\V1\WorkflowInstanceController;
use Illuminate\Support\Facades\Route;

Route::apiResource('workflow-definitions', WorkflowDefinitionController::class)->except(['create', 'edit']);
Route::post('workflow-definitions/{definition}/configuration', [WorkflowDefinitionController::class, 'storeConfiguration'])->name('workflow-definitions.configuration.store');
Route::put('workflow-definitions/{definition}/configuration', [WorkflowDefinitionController::class, 'updateConfiguration'])->name('workflow-definitions.configuration.update');

// Pas d'`update` pour les instances (le Blade n'en expose pas : l'évolution passe par
// les actions start/pause/resume/cancel).
Route::apiResource('workflow-instances', WorkflowInstanceController::class)->only(['index', 'store', 'show', 'destroy']);
Route::post('workflow-instances/{instance}/start', [WorkflowInstanceController::class, 'start'])->name('workflow-instances.start');
Route::post('workflow-instances/{instance}/pause', [WorkflowInstanceController::class, 'pause'])->name('workflow-instances.pause');
Route::post('workflow-instances/{instance}/resume', [WorkflowInstanceController::class, 'resume'])->name('workflow-instances.resume');
Route::post('workflow-instances/{instance}/cancel', [WorkflowInstanceController::class, 'cancel'])->name('workflow-instances.cancel');
