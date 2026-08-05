<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ContainerStatus\StoreContainerStatusRequest;
use App\Http\Requests\Api\V1\ContainerStatus\UpdateContainerStatusRequest;
use App\Http\Resources\Api\V1\ContainerStatusResource;
use App\Models\ContainerStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D03 — relu et validé le 2026-08-04 contre `ContainerStatusController` et le schéma.
 *
 * Référentiel global (statuts de conteneurs). Les routes `access` du contrôleur Blade
 * pointent le même `ContainerStatusController` : fusionnées ici.
 * `creator_id` posé depuis l'agent.
 */
class ContainerStatusController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['creator'];

    /**
     * GET /api/v1/container-statuses
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ContainerStatus::class);

        $query = ContainerStatus::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ContainerStatusResource::class));
    }

    /**
     * GET /api/v1/container-statuses/{id}
     */
    public function show(ContainerStatus $containerStatus): JsonResponse
    {
        $this->authorize('view', $containerStatus);

        return response()->json(['data' => new ContainerStatusResource($containerStatus)]);
    }

    /**
     * POST /api/v1/container-statuses
     */
    public function store(StoreContainerStatusRequest $request): JsonResponse
    {
        $this->authorize('create', ContainerStatus::class);

        $status = ContainerStatus::create($request->validated() + ['creator_id' => Auth::id()]);

        return response()->json(
            ['data' => new ContainerStatusResource($status)],
            201,
            ['Location' => "/api/v1/container-statuses/{$status->id}"]
        );
    }

    /**
     * PATCH /api/v1/container-statuses/{id}
     */
    public function update(UpdateContainerStatusRequest $request, ContainerStatus $containerStatus): JsonResponse
    {
        $this->authorize('update', $containerStatus);

        $containerStatus->update($request->validated());

        return response()->json(['data' => new ContainerStatusResource($containerStatus->fresh())]);
    }

    /**
     * DELETE /api/v1/container-statuses/{id}
     */
    public function destroy(ContainerStatus $containerStatus): Response
    {
        $this->authorize('delete', $containerStatus);

        $containerStatus->delete();

        return response()->noContent();
    }
}
