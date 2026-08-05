<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

/**
 * D02 — Drag & Drop de notices avec IA.
 *
 * TODO — NON PORTÉ (action complexe, abandon documenté). Justification technique :
 *  - Le contrôleur Blade orchestre une pipeline lourde : upload multiple, extraction
 *    de texte (`AttachmentTextExtractor`), appel IA (`ProviderRegistry`/`AiBridge` avec
 *    timeouts `env('AI_REQUEST_TIMEOUT')`), parsing JSON, persistance d'une notice +
 *    auteurs + mots-clés, suggestion/fallback d'activité par organisation. Cette
 *    orchestration dépend d'infrastructure IA (settings `ai_default_provider` /
 *    `ai_default_model`, fournisseurs externes, settings de taille d'upload) hors du
 *    contrat de données REST de la phase 1.
 *  - Le portage suppose aussi la résolution des modèles legacy encore référencés
 *    (`RecordPhysical`, `Attachment::TYPE_*`) et du calcul du code (10 caractères).
 *  - À porter : un endpoint `POST /records/drag-drop/process` org-scopé, calqué sur
 *    `processDragDrop`, avec FileRequest dédié et Policy `records_create` — relecture
 *    du service IA préalable (sync/async).
 */
class RecordDragDropController extends Controller
{
}
