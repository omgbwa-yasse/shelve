<?php

use App\Http\Controllers\Api\V1\BackupController;
use App\Http\Controllers\Api\V1\BackupFileController;
use App\Http\Controllers\Api\V1\BackupPlanningController;
use App\Http\Controllers\Api\V1\LogController;

// Routes API v1 du domaine D16 — Exploitation (porté le 2026-08-04).
// Inclus dans le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.
//
// ⚠️ Non portées (TODO E2, phase 3) : exports binaires (BarcodeController, PDFController,
// PhantomController, SEDAExportController) et actions d'exploitation sans table dédiée
// (NewFeatureController, RateLimitController, SystemUpdateController, ReportController).

Route::apiResource('backups', BackupController::class)->except(['create', 'edit']);
Route::apiResource('backup-files', BackupFileController::class)->except(['create', 'edit']);
Route::apiResource('backup-plannings', BackupPlanningController::class)->except(['create', 'edit']);
Route::apiResource('logs', LogController::class)->except(['create', 'edit']);
