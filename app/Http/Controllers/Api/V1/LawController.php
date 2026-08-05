<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Law\StoreLawRequest;
use App\Http\Requests\Api\V1\Law\UpdateLawRequest;
use App\Http\Resources\Api\V1\LawResource;
use App\Models\Law;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre le contrôleur Blade et le schéma.
 */
class LawController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'code', 'name', 'publish_date', 'law_type_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'publish_date', 'law_type_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/laws
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Law::class);

        $query = Law::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, LawResource::class));
    }

    /**
     * GET /api/v1/laws/{id}
     */
    public function show(Law $law): JsonResponse
    {
        $this->authorize('view', $law);

        return response()->json(['data' => new LawResource($law)]);
    }

    /**
     * POST /api/v1/laws
     */
    public function store(StoreLawRequest $request): JsonResponse
    {
        $this->authorize('create', Law::class);

        $law = Law::create($request->validated());

        return response()->json(
            ['data' => new LawResource($law)],
            201,
            ['Location' => "/api/v1/laws/{$law->id}"]
        );
    }

    /**
     * PATCH /api/v1/laws/{id}
     */
    public function update(UpdateLawRequest $request, Law $law): JsonResponse
    {
        $this->authorize('update', $law);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $law->update($request->validated());

        return response()->json(['data' => new LawResource($law->fresh())]);
    }

    /**
     * DELETE /api/v1/laws/{id}
     */
    public function destroy(Law $law): Response
    {
        $this->authorize('delete', $law);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $law->delete();

        return response()->noContent();
    }
}
