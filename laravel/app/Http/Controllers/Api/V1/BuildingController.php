<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Building\StoreBuildingRequest;
use App\Http\Requests\Api\V1\Building\UpdateBuildingRequest;
use App\Http\Resources\Api\V1\BuildingResource;
use App\Models\Building;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D03 — relu et validé le 2026-08-04 contre `BuildingController` et le schéma.
 *
 * Les bâtiments sont des référentiels globaux (pas d'organisation). `creator_id` est
 * posé depuis l'agent authentifié (le Blade utilisait un `1` codé en dur — corrigé).
 * Les routes `trolleys` du contrôleur Blade pointent le même `BuildingController` :
 * elles sont fusionnées ici (une seule ressource `buildings`).
 */
class BuildingController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'visibility', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'visibility', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['floors', 'creator'];

    /**
     * GET /api/v1/buildings
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Building::class);

        $query = Building::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, BuildingResource::class));
    }

    /**
     * GET /api/v1/buildings/{id}
     */
    public function show(Building $building): JsonResponse
    {
        $this->authorize('view', $building);

        return response()->json(['data' => new BuildingResource($building)]);
    }

    /**
     * POST /api/v1/buildings
     */
    public function store(StoreBuildingRequest $request): JsonResponse
    {
        $this->authorize('create', Building::class);

        $building = Building::create($request->validated() + ['creator_id' => Auth::id()]);

        return response()->json(
            ['data' => new BuildingResource($building)],
            201,
            ['Location' => "/api/v1/buildings/{$building->id}"]
        );
    }

    /**
     * PATCH /api/v1/buildings/{id}
     */
    public function update(UpdateBuildingRequest $request, Building $building): JsonResponse
    {
        $this->authorize('update', $building);

        $building->update($request->validated());

        return response()->json(['data' => new BuildingResource($building->fresh())]);
    }

    /**
     * DELETE /api/v1/buildings/{id}
     */
    public function destroy(Building $building): Response
    {
        $this->authorize('delete', $building);

        $building->delete();

        return response()->noContent();
    }
}
