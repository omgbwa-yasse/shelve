<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Retention\StoreRetentionRequest;
use App\Http\Requests\Api\V1\Retention\UpdateRetentionRequest;
use App\Http\Resources\Api\V1\RetentionResource;
use App\Models\Retention;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D07 — relu et validé le 2026-08-04 contre `RetentionController` (Blade) et le schéma.
 *
 * Les durées de conservation (`retentions`) sont un référentiel global (pas
 * d'organisation), motif D01 : aucune borne d'organisation sur l'index, aucune
 * recherche `inOrganisation` sur les autres méthodes.
 */
class RetentionController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'duration', 'sort_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'duration', 'sort_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['sort', 'activities'];

    /**
     * GET /api/v1/retentions
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Retention::class);

        $query = Retention::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RetentionResource::class));
    }

    /**
     * GET /api/v1/retentions/{id}
     */
    public function show(Retention $retention): JsonResponse
    {
        $this->authorize('view', $retention);

        return response()->json(['data' => new RetentionResource($retention)]);
    }

    /**
     * POST /api/v1/retentions
     */
    public function store(StoreRetentionRequest $request): JsonResponse
    {
        $this->authorize('create', Retention::class);

        $retention = Retention::create($request->validated());

        return response()->json(
            ['data' => new RetentionResource($retention->load('sort'))],
            201,
            ['Location' => "/api/v1/retentions/{$retention->id}"]
        );
    }

    /**
     * PATCH /api/v1/retentions/{id}
     */
    public function update(UpdateRetentionRequest $request, Retention $retention): JsonResponse
    {
        $this->authorize('update', $retention);

        $retention->update($request->validated());

        return response()->json(['data' => new RetentionResource($retention->fresh())]);
    }

    /**
     * DELETE /api/v1/retentions/{id}
     */
    public function destroy(Retention $retention): Response
    {
        $this->authorize('delete', $retention);

        $retention->delete();

        return response()->noContent();
    }
}
