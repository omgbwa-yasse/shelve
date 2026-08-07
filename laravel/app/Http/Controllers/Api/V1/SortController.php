<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Sort\StoreSortRequest;
use App\Http\Requests\Api\V1\Sort\UpdateSortRequest;
use App\Http\Resources\Api\V1\SortResource;
use App\Models\Sort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre le contrôleur Blade et le schéma.
 */
class SortController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'code', 'name', 'description', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'description', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/sorts
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Sort::class);

        $query = Sort::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, SortResource::class));
    }

    /**
     * GET /api/v1/sorts/{id}
     */
    public function show(Sort $sort): JsonResponse
    {
        $this->authorize('view', $sort);

        return response()->json(['data' => new SortResource($sort)]);
    }

    /**
     * POST /api/v1/sorts
     */
    public function store(StoreSortRequest $request): JsonResponse
    {
        $this->authorize('create', Sort::class);

        $sort = Sort::create($request->validated());

        return response()->json(
            ['data' => new SortResource($sort)],
            201,
            ['Location' => "/api/v1/sorts/{$sort->id}"]
        );
    }

    /**
     * PATCH /api/v1/sorts/{id}
     */
    public function update(UpdateSortRequest $request, Sort $sort): JsonResponse
    {
        $this->authorize('update', $sort);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $sort->update($request->validated());

        return response()->json(['data' => new SortResource($sort->fresh())]);
    }

    /**
     * DELETE /api/v1/sorts/{id}
     */
    public function destroy(Sort $sort): Response
    {
        $this->authorize('delete', $sort);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $sort->delete();

        return response()->noContent();
    }
}
