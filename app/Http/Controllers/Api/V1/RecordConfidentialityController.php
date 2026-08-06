<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RecordConfidentialityResource;
use App\Models\Record;
use App\Models\RecordConfidentiality;
use Illuminate\Http\JsonResponse;

/**
 * D02 — référentiel des niveaux de confidentialité, utilisé pour peupler le
 * sélecteur "Confidentialité" du formulaire de notice. Lecture seule, même
 * choix que `RecordLevelController` (pas de policy dédiée).
 */
class RecordConfidentialityController extends Controller
{
    /**
     * GET /api/v1/record-confidentialities
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        return response()->json(['data' => RecordConfidentialityResource::collection(RecordConfidentiality::orderBy('name')->get())]);
    }
}
