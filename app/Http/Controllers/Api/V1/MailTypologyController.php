<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MailTypology\StoreMailTypologyRequest;
use App\Http\Requests\Api\V1\MailTypology\UpdateMailTypologyRequest;
use App\Http\Resources\Api\V1\MailTypologyResource;
use App\Models\MailTypology;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D06 — relu et validé le 2026-08-04 contre `MailTypologyController` et le schéma.
 *
 * Les typologies de courrier (`mail_typologies`) sont des référentiels globaux,
 * partagés entre organisations (motif D01). L'unicité du nom est portée par les
 * règles `unique` / `Rule::unique()->ignore`.
 *
 * Divergence assumée : `code` (colonne NOT NULL, sans défaut) est exigé en création,
 * alors que le Blade ne le posait pas — une insertion sans code aurait de toute façon
 * échoué en SQL (erreur 1364). Le Blade portait donc un bug latent que l'API corrige.
 */
class MailTypologyController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'activity_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'activity_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['activity', 'mails'];

    /**
     * GET /api/v1/mail-typologies
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MailTypology::class);

        $query = MailTypology::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, MailTypologyResource::class));
    }

    /**
     * GET /api/v1/mail-typologies/{id}
     */
    public function show(MailTypology $mailTypology): JsonResponse
    {
        $this->authorize('view', $mailTypology);

        return response()->json(['data' => new MailTypologyResource($mailTypology)]);
    }

    /**
     * POST /api/v1/mail-typologies
     */
    public function store(StoreMailTypologyRequest $request): JsonResponse
    {
        $this->authorize('create', MailTypology::class);

        $mailTypology = MailTypology::create($request->validated());

        return response()->json(
            ['data' => new MailTypologyResource($mailTypology)],
            201,
            ['Location' => "/api/v1/mail-typologies/{$mailTypology->id}"]
        );
    }

    /**
     * PATCH /api/v1/mail-typologies/{id}
     */
    public function update(UpdateMailTypologyRequest $request, MailTypology $mailTypology): JsonResponse
    {
        $this->authorize('update', $mailTypology);

        $mailTypology->update($request->validated());

        return response()->json(['data' => new MailTypologyResource($mailTypology->fresh())]);
    }

    /**
     * DELETE /api/v1/mail-typologies/{id}
     */
    public function destroy(MailTypology $mailTypology): Response
    {
        $this->authorize('delete', $mailTypology);

        $mailTypology->delete();

        return response()->noContent();
    }
}
