<?php

// Routes API v1 du domaine D18 — Assistant IA (panneau latéral : Chat,
// Routine, Historique). Voir demande utilisateur du 2026-08-05. Inclus dans
// le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.

use App\Http\Controllers\Api\V1\AiConversationController;
use App\Http\Controllers\Api\V1\AiRoutineController;
use Illuminate\Support\Facades\Route;

// Conversations (onglets Chat + Historique) — ressource personnelle, pas d'apiResource
// standard : `store` prend le premier message, `sendMessage` en ajoute d'autres.
Route::get('ai/conversations', [AiConversationController::class, 'index'])->name('ai.conversations.index');
Route::post('ai/conversations', [AiConversationController::class, 'store'])->name('ai.conversations.store');
Route::get('ai/conversations/{conversation}', [AiConversationController::class, 'show'])->name('ai.conversations.show');
Route::post('ai/conversations/{conversation}/messages', [AiConversationController::class, 'sendMessage'])->name('ai.conversations.messages.store');
Route::delete('ai/conversations/{conversation}', [AiConversationController::class, 'destroy'])->name('ai.conversations.destroy');

// Routines programmées (onglet Routine) — connectées aux prompts/skills IA (D14).
Route::post('ai/routines/{aiRoutine}/run', [AiRoutineController::class, 'run'])->name('ai.routines.run');
Route::apiResource('ai/routines', AiRoutineController::class)->except(['create', 'edit'])->parameters(['routines' => 'aiRoutine']);
