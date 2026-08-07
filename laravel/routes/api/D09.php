<?php

// Routes API v1 du domaine D09 — Organisation & sécurité (porté le 2026-08-05).
// Ce fichier est inclus dans le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.

use App\Http\Controllers\Api\V1\OrganisationController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserOrganisationRoleController;
use App\Http\Controllers\Api\V1\UserRoleController;

// Référentiels globaux.
Route::apiResource('organisations', OrganisationController::class)->except(['create', 'edit']);
Route::apiResource('users', UserController::class)->except(['create', 'edit']);
Route::apiResource('roles', RoleController::class)->except(['create', 'edit']);

// Pivot global (table avec `id`).
Route::apiResource('user-roles', UserRoleController::class)->except(['create', 'edit']);

// `user-organisation-roles` : clé primaire composite (user_id, organisation_id),
// PAS de colonne `id` — show/update/destroy déclarés avec la paire {user}/{organisation}.
Route::get('user-organisation-roles', [UserOrganisationRoleController::class, 'index'])
    ->name('user-organisation-roles.index');
Route::post('user-organisation-roles', [UserOrganisationRoleController::class, 'store'])
    ->name('user-organisation-roles.store');
Route::get('user-organisation-roles/{user}/{organisation}', [UserOrganisationRoleController::class, 'show'])
    ->name('user-organisation-roles.show');
Route::patch('user-organisation-roles/{user}/{organisation}', [UserOrganisationRoleController::class, 'update'])
    ->name('user-organisation-roles.update');
Route::delete('user-organisation-roles/{user}/{organisation}', [UserOrganisationRoleController::class, 'destroy'])
    ->name('user-organisation-roles.destroy');

// TODO D09 — pivots/actions non portées (étape 1.x) :
//  - organisations/{organisation}/activities : pivot `organisation_activity`
//    composite sans id, rattachement par attach/detach — endpoint imbriqué à décider.
//  - organisations/{organisation}/contacts : pivot `organisation_contact` composite
//    sans id, modèle Contact global — endpoint imbriqué à décider.
//  - roles/{role}/permissions : `RolePermissionController` est une matrice de
//    synchronisation globale (detach+attach), pas une ressource par ligne.
//  - organisation-active : table `organisation_active` absente du schéma baseline
//    (modèle et contrôleur cassés, FIXME D09).
