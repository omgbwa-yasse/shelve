<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RecordLevelResource;
use App\Models\Record;
use App\Models\RecordLevel;
use Illuminate\Http\JsonResponse;

/**
 * D02 — référentiel des niveaux de description (fonds/série/dossier/pièce…),
 * utilisé pour peupler le sélecteur "Niveau" du formulaire de notice.
 * Lecture seule : géré aujourd'hui via seed, pas d'écran d'administration
 * dédié — gardé derrière la même permission que les notices (`records_view`)
 * plutôt que de créer une policy dédiée pour un référentiel figé.
 */
class RecordLevelController extends Controller
{
    /**
     * GET /api/v1/record-levels
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        return response()->json(['data' => RecordLevelResource::collection(RecordLevel::orderBy('name')->get())]);
    }
}
