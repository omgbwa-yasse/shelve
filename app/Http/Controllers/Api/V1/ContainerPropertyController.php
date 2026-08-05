<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ContainerProperty\StoreContainerPropertyRequest;
use App\Http\Requests\Api\V1\ContainerProperty\UpdateContainerPropertyRequest;
use App\Http\Resources\Api\V1\ContainerPropertyResource;
use App\Models\ContainerProperty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D03 — relu et validé le 2026-08-04 contre `ContainerPropertyController` et le schéma.
 *
 * Référentiel global (types de conteneurs). `creator_id` posé depuis l'agent.
 */
class ContainerPropertyController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['creator'];

    /**
     * GET /api/v1/container-properties
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ContainerProperty::class);

        $query = ContainerProperty::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ContainerPropertyResource::class));
    }

    /**
     * GET /api/v1/container-properties/{id}
     */
    public function show(ContainerProperty $containerProperty): JsonResponse
    {
        $this->authorize('view', $containerProperty);

        return response()->json(['data' => new ContainerPropertyResource($containerProperty)]);
    }

    /**
     * POST /api/v1/container-properties
     */
    public function store(StoreContainerPropertyRequest $request): JsonResponse
    {
        $this->authorize('create', ContainerProperty::class);

        $property = ContainerProperty::create($request->validated() + ['creator_id' => Auth::id()]);

        return response()->json(
            ['data' => new ContainerPropertyResource($property)],
            201,
            ['Location' => "/api/v1/container-properties/{$property->id}"]
        );
    }

    /**
     * PATCH /api/v1/container-properties/{id}
     */
    public function update(UpdateContainerPropertyRequest $request, ContainerProperty $containerProperty): JsonResponse
    {
        $this->authorize('update', $containerProperty);

        $containerProperty->update($request->validated());

        return response()->json(['data' => new ContainerPropertyResource($containerProperty->fresh())]);
    }

    /**
     * DELETE /api/v1/container-properties/{id}
     */
    public function destroy(ContainerProperty $containerProperty): Response
    {
        $this->authorize('delete', $containerProperty);

        $containerProperty->delete();

        return response()->noContent();
    }
}
