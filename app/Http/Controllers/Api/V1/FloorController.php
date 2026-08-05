<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Floor\StoreFloorRequest;
use App\Http\Requests\Api\V1\Floor\UpdateFloorRequest;
use App\Http\Resources\Api\V1\FloorResource;
use App\Models\Floor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D03 — relu et validé le 2026-08-04 contre `FloorController` et le schéma.
 *
 * Les étages sont des référentiels globaux (pas d'organisation), rattachés à un
 * bâtiment via `building_id`. `creator_id` est posé depuis l'agent authentifié.
 */
class FloorController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'building_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'building_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['building', 'rooms', 'creator'];

    /**
     * GET /api/v1/floors
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Floor::class);

        $query = Floor::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, FloorResource::class));
    }

    /**
     * GET /api/v1/floors/{id}
     */
    public function show(Floor $floor): JsonResponse
    {
        $this->authorize('view', $floor);

        return response()->json(['data' => new FloorResource($floor)]);
    }

    /**
     * POST /api/v1/floors
     */
    public function store(StoreFloorRequest $request): JsonResponse
    {
        $this->authorize('create', Floor::class);

        $floor = Floor::create($request->validated() + ['creator_id' => Auth::id()]);

        return response()->json(
            ['data' => new FloorResource($floor)],
            201,
            ['Location' => "/api/v1/floors/{$floor->id}"]
        );
    }

    /**
     * PATCH /api/v1/floors/{id}
     */
    public function update(UpdateFloorRequest $request, Floor $floor): JsonResponse
    {
        $this->authorize('update', $floor);

        $floor->update($request->validated());

        return response()->json(['data' => new FloorResource($floor->fresh())]);
    }

    /**
     * DELETE /api/v1/floors/{id}
     */
    public function destroy(Floor $floor): Response
    {
        $this->authorize('delete', $floor);

        $floor->delete();

        return response()->noContent();
    }
}
