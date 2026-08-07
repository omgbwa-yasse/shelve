<?php

// Routes API v1 du domaine D08 — Thésaurus SKOS (porté le 2026-08-04, import le 2026-08-05).
// Ce fichier est inclus dans le groupe api.v1. (auth:sanctum + rate.limit) de routes/api.php.

use App\Http\Controllers\Api\V1\ThesaurusConceptController;
use App\Http\Controllers\Api\V1\ThesaurusImportController;
use App\Http\Controllers\Api\V1\ThesaurusSchemeController;

// Actions métier déclarées AVANT les apiResource : `search` et `autocomplete`
// ne doivent pas être capturées par le paramètre `{thesaurus_concept}`.
Route::get('thesaurus-concepts/search', [ThesaurusConceptController::class, 'search'])->name('thesaurus-concepts.search');
Route::get('thesaurus-concepts/autocomplete', [ThesaurusConceptController::class, 'autocomplete'])->name('thesaurus-concepts.autocomplete');

Route::apiResource('thesaurus-schemes', ThesaurusSchemeController::class)->except(['create', 'edit']);
Route::apiResource('thesaurus-concepts', ThesaurusConceptController::class)->except(['create', 'edit']);

// Import thésaurus (porté le 2026-08-05) — `imports` déclaré avant la route statique
// `import` pour éviter toute collision d'ordre.
Route::post('thesaurus/import', [ThesaurusImportController::class, 'import'])->name('thesaurus.import');
Route::get('thesaurus/imports/{import}', [ThesaurusImportController::class, 'show'])->name('thesaurus.imports.show');

// TODO D08 — actions métier complexes non portées (étape 1.x) :
//  - export thésaurus (ThesaurusController::exportScheme / export SKOS-RDF/CSV/JSON) :
//    classe E2 (phase 3), exports de fichiers binaires — TODO documenté.
//  - hiérarchies profondes (ThesaurusController::hierarchy, buildHierarchyTree).
//  - relations hiérarchiques (storeBroaderRelation / storeNarrowerRelation / destroyHierarchicalRelation).
//  - statistiques (ThesaurusController::statistics), autoAssociateConcepts, recordConceptRelations.
//  - traductions (ThesaurusTranslationController) et relations associatives
//    (ThesaurusAssociativeRelationController) : pivots sans modèle dédié, à porter
//    quand les tables `translations` / `associative_relations` auront un modèle.
