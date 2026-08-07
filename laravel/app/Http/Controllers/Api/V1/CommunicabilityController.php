<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Communicability\StoreCommunicabilityRequest;
use App\Http\Requests\Api\V1\Communicability\UpdateCommunicabilityRequest;
use App\Http\Resources\Api\V1\CommunicabilityResource;
use App\Models\Communicability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre le contrôleur Blade et le schéma.
 */
class CommunicabilityController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'code', 'name', 'duration', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'duration', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/communicabilities
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Communicability::class);

        $query = Communicability::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, CommunicabilityResource::class));
    }

    /**
     * GET /api/v1/communicabilities/{id}
     */
    public function show(Communicability $communicability): JsonResponse
    {
        $this->authorize('view', $communicability);

        return response()->json(['data' => new CommunicabilityResource($communicability)]);
    }

    /**
     * POST /api/v1/communicabilities
     */
    public function store(StoreCommunicabilityRequest $request): JsonResponse
    {
        $this->authorize('create', Communicability::class);

        $communicability = Communicability::create($request->validated());

        return response()->json(
            ['data' => new CommunicabilityResource($communicability)],
            201,
            ['Location' => "/api/v1/communicabilities/{$communicability->id}"]
        );
    }

    /**
     * PATCH /api/v1/communicabilities/{id}
     */
    public function update(UpdateCommunicabilityRequest $request, Communicability $communicability): JsonResponse
    {
        $this->authorize('update', $communicability);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $communicability->update($request->validated());

        return response()->json(['data' => new CommunicabilityResource($communicability->fresh())]);
    }

    /**
     * DELETE /api/v1/communicabilities/{id}
     */
    public function destroy(Communicability $communicability): Response
    {
        $this->authorize('delete', $communicability);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $communicability->delete();

        return response()->noContent();
    }
}
