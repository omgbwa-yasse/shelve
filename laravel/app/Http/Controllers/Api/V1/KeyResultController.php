<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\KeyResult\StoreKeyResultRequest;
use App\Http\Requests\Api\V1\KeyResult\UpdateKeyResultRequest;
use App\Http\Resources\Api\V1\KeyResultResource;
use App\Models\KeyResult;
use App\Models\Objective;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D17 — Résultats clés (OKR), toujours enfants d'un objectif. Voir
 * `evolution/PROJECT-OKR-KPI-PLAN.md`, §3.
 */
class KeyResultController extends Controller
{
    /**
     * GET /api/v1/objectives/{objective}/key-results
     */
    public function index(Objective $objective): JsonResponse
    {
        $this->authorize('view', $objective);

        $objective = Objective::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($objective->id);

        return response()->json(['data' => KeyResultResource::collection($objective->keyResults)]);
    }

    /**
     * POST /api/v1/objectives/{objective}/key-results
     */
    public function store(StoreKeyResultRequest $request, Objective $objective): JsonResponse
    {
        $this->authorize('update', $objective);

        $objective = Objective::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($objective->id);

        $keyResult = $objective->keyResults()->create($request->validated());

        return response()->json(
            ['data' => new KeyResultResource($keyResult)],
            201,
            ['Location' => "/api/v1/key-results/{$keyResult->id}"]
        );
    }

    /**
     * PATCH /api/v1/key-results/{id} — couvre l'édition complète et la mise à
     * jour rapide de la progression (`current_value` seul).
     */
    public function update(UpdateKeyResultRequest $request, KeyResult $keyResult): JsonResponse
    {
        $keyResult->load('objective');
        $this->authorize('updateKeyResult', $keyResult->objective);

        $objective = Objective::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($keyResult->objective_id);
        abort_unless($keyResult->objective_id === $objective->id, 404);

        $keyResult->update($request->validated());

        return response()->json(['data' => new KeyResultResource($keyResult->fresh())]);
    }

    /**
     * DELETE /api/v1/key-results/{id}
     */
    public function destroy(KeyResult $keyResult): Response
    {
        $keyResult->load('objective');
        $this->authorize('update', $keyResult->objective);

        $objective = Objective::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($keyResult->objective_id);
        abort_unless($keyResult->objective_id === $objective->id, 404);

        $keyResult->delete();

        return response()->noContent();
    }
}
