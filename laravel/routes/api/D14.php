<?php

use App\Http\Controllers\Api\V1\AiSkillController;
use App\Http\Controllers\Api\V1\AiTemplateController;
use App\Http\Controllers\Api\V1\PromptController;

// Routes API v1 du domaine D14 — IA (porté le 2026-08-04).
// Inclus dans le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.
//
// ⚠️ Non portées (TODO E2, phase 3) : appels IA et lectures de fichiers —
// PromptController::actions(), AiSearchController (chat/documentation),
// AiSearchTestController (runTests/runTest/testInterface), AiResourceController::index,
// AiSkillController::install, AiTemplateController::download/preview.

// L'action `toggle` précède l'apiResource : sinon `{aiSkill}` capturerait `toggle`.
Route::post('ai-skills/{aiSkill}/toggle', [AiSkillController::class, 'toggle'])->name('ai-skills.toggle');

Route::apiResource('ai-skills', AiSkillController::class)->except(['create', 'edit']);
Route::apiResource('ai-templates', AiTemplateController::class)->except(['create', 'edit']);
Route::apiResource('prompts', PromptController::class)->except(['create', 'edit']);
