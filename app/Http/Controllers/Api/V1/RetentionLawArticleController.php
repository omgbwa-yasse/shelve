<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RetentionLawArticle\StoreRetentionLawArticleRequest;
use App\Http\Requests\Api\V1\RetentionLawArticle\UpdateRetentionLawArticleRequest;
use App\Http\Resources\Api\V1\RetentionLawArticleResource;
use App\Models\RetentionLawArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D07 — exigence réglementaire (liaison rétention ↔ article de loi, pivot
 * `retention_law_articles`). Le contrôleur Blade est vide : le CRUD est reconstruit
 * à partir du modèle et du schéma.
 *
 * Le pivot n'a PAS de colonne `id` (clé composite retention_id + law_article_id) :
 * `show`/`update` n'ont pas de sens en REST et `destroy` est porté par la paire
 * {retention}/{lawArticle}. Écart assumé au gabarit apiResource (routes/api/D07.php).
 *
 * Référentiel global (motif D01) : les deux côtés sont des référentiels globaux.
 */
class RetentionLawArticleController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['retention_id', 'law_article_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['retention_id', 'law_article_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/retention-law-articles
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RetentionLawArticle::class);

        $query = RetentionLawArticle::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'retention_id');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RetentionLawArticleResource::class));
    }

    /**
     * POST /api/v1/retention-law-articles
     */
    public function store(StoreRetentionLawArticleRequest $request): JsonResponse
    {
        $this->authorize('create', RetentionLawArticle::class);

        $retentionLawArticle = RetentionLawArticle::firstOrCreate($request->validated());

        return response()->json(
            ['data' => new RetentionLawArticleResource($retentionLawArticle)],
            201,
            ['Location' => "/api/v1/retention-law-articles/{$retentionLawArticle->retention_id}/{$retentionLawArticle->law_article_id}"]
        );
    }

    /**
     * PATCH /api/v1/retention-law-articles/{retention}/{lawArticle}
     *
     * Mise à jour par clé composite : le Query Builder remplace l'instance `update()`
     * d'Eloquent (qui s'appuierait sur la clé primaire `id`, absente de la table).
     */
    public function update(UpdateRetentionLawArticleRequest $request, int $retention, int $lawArticle): JsonResponse
    {
        $pivot = RetentionLawArticle::query()
            ->where('retention_id', $retention)
            ->where('law_article_id', $lawArticle)
            ->firstOrFail();

        $this->authorize('update', $pivot);

        RetentionLawArticle::query()
            ->where('retention_id', $retention)
            ->where('law_article_id', $lawArticle)
            ->update($request->validated());

        $fresh = RetentionLawArticle::query()
            ->where('retention_id', $retention)
            ->where('law_article_id', $lawArticle)
            ->firstOrFail();

        return response()->json(['data' => new RetentionLawArticleResource($fresh)]);
    }

    /**
     * DELETE /api/v1/retention-law-articles/{retention}/{lawArticle}
     */
    public function destroy(int $retention, int $lawArticle): Response
    {
        $pivot = RetentionLawArticle::query()
            ->where('retention_id', $retention)
            ->where('law_article_id', $lawArticle)
            ->firstOrFail();

        $this->authorize('delete', $pivot);

        RetentionLawArticle::query()
            ->where('retention_id', $retention)
            ->where('law_article_id', $lawArticle)
            ->delete();

        return response()->noContent();
    }
}
