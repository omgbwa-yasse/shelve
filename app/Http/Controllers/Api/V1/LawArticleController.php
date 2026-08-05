<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LawArticle\StoreLawArticleRequest;
use App\Http\Requests\Api\V1\LawArticle\UpdateLawArticleRequest;
use App\Http\Resources\Api\V1\LawArticleResource;
use App\Models\LawArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre le contrôleur Blade et le schéma.
 */
class LawArticleController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'code', 'name', 'law_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'law_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/law-articles
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LawArticle::class);

        $query = LawArticle::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, LawArticleResource::class));
    }

    /**
     * GET /api/v1/law-articles/{id}
     */
    public function show(LawArticle $lawArticle): JsonResponse
    {
        $this->authorize('view', $lawArticle);

        return response()->json(['data' => new LawArticleResource($lawArticle)]);
    }

    /**
     * POST /api/v1/law-articles
     */
    public function store(StoreLawArticleRequest $request): JsonResponse
    {
        $this->authorize('create', LawArticle::class);

        $lawArticle = LawArticle::create($request->validated());

        return response()->json(
            ['data' => new LawArticleResource($lawArticle)],
            201,
            ['Location' => "/api/v1/law-articles/{$lawArticle->id}"]
        );
    }

    /**
     * PATCH /api/v1/law-articles/{id}
     */
    public function update(UpdateLawArticleRequest $request, LawArticle $lawArticle): JsonResponse
    {
        $this->authorize('update', $lawArticle);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $lawArticle->update($request->validated());

        return response()->json(['data' => new LawArticleResource($lawArticle->fresh())]);
    }

    /**
     * DELETE /api/v1/law-articles/{id}
     */
    public function destroy(LawArticle $lawArticle): Response
    {
        $this->authorize('delete', $lawArticle);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $lawArticle->delete();

        return response()->noContent();
    }
}
