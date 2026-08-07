<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * D11 — actions en masse sur les éléments d'un chariot (`GET /dollies/action`).
 *
 * ⚠️ TODO DOCUMENTÉ — partiellement porté.
 *
 * Le contrôleur Blade `DollyActionController` est un routeur de formulaires qui
 * renvoie des vues, avec plus de 25 combinaisons catégorie × action (dates,
 * priorité, type, niveau, statut, conteneur, activité, support, export SEDA/PDF…).
 *
 * Actions portées depuis le 2026-08-05 (points d'entrée explicites, logique simple) :
 *   - ajout/retrait d'éléments : `DollyController` `add-record` / `remove-record`,
 *     `add-communication` / `remove-communication`, `add-slip` / `remove-slip`
 *     (et les autres catégories déjà exposées) ;
 *   - `clear` (équivalent des `clean` du Blade : détache les pivots sans supprimer
 *     les entités) et `rename` (renommage du chariot).
 *
 * Actions NON portées (501), avec la raison technique :
 *   - **`*Delete`** (suppression physique des entités du chariot, ex. `mailDelete`)
 *     : destructif, hors du périmètre d'édition de l'API phase 1 — à réexposer par une
 *     action dédiée après relecture métier.
 *   - **mass-edit en masse** (dates, priorité, type, niveau, statut, conteneur,
 *     activité, support, salle/étagère) : encodent des colonnes incohérentes avec le
 *     schéma actuel — `MailDateChange` écrit `mails.date_exact` (colonne absente),
 *     `MailTypeChange` écrit `mails.type_id` (colonne absente), `MailArchivedChange`
 *     écrit `mails.is_achived` (typo de `is_archived`), `ContainerShelfchange` itère
 *     `slipRecords` au lieu des conteneurs, la relation « shelves » du modèle est
 *     `shelve()`. Un portage approximatif encoderait ces bugs : relecture métier
 *     préalable, chaque édition en masse doit être repensée en point d'entrée dédié.
 *   - **exports** (SEDA XML, inventaire PDF) : classe E2 (phase 3) — exports de fichiers.
 *
 * Le GET `/dollies/action` du Blade (routeur de formulaires) reste non exposé ; les
 * actions portées ont leurs propres routes explicites.
 */
class DollyActionController extends Controller
{
    /**
     * GET /api/v1/dollies/action
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'type' => 'about:blank',
            'title' => 'Non implémenté',
            'status' => 501,
            'detail' => 'Le routeur d\'actions en masse de DollyActionController n\'est pas exposé : utiliser les points d\'entrée explicites add-*/remove-*, clear et rename (voir l\'en-tête).',
        ], 501);
    }
}
