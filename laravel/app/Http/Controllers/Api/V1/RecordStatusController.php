<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordStatus\StoreRecordStatusRequest;
use App\Http\Requests\Api\V1\RecordStatus\UpdateRecordStatusRequest;
use App\Http\Resources\Api\V1\RecordStatusResource;
use App\Models\RecordStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D02 — sous-référentiel global (statuts de notices). Relu le 2026-08-04 contre
 * `RecordStatusController` et le schéma (table `record_statuses`).
 *
 * Référentiel global : aucune portée organisation. Les routes `/settings/record-statuses`
 * et `/tools/record-statuses` du Blade pointent le même contrôleur : fusionnées ici.
 */
class RecordStatusController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/record-statuses
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecordStatus::class);

        $query = RecordStatus::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordStatusResource::class));
    }

    /**
     * GET /api/v1/record-statuses/{id}
     */
    public function show(RecordStatus $recordStatus): JsonResponse
    {
        $this->authorize('view', $recordStatus);

        return response()->json(['data' => new RecordStatusResource($recordStatus)]);
    }

    /**
     * POST /api/v1/record-statuses
     */
    public function store(StoreRecordStatusRequest $request): JsonResponse
    {
        $this->authorize('create', RecordStatus::class);

        $recordStatus = RecordStatus::create($request->validated());

        return response()->json(
            ['data' => new RecordStatusResource($recordStatus)],
            201,
            ['Location' => "/api/v1/record-statuses/{$recordStatus->id}"]
        );
    }

    /**
     * PATCH /api/v1/record-statuses/{id}
     */
    public function update(UpdateRecordStatusRequest $request, RecordStatus $recordStatus): JsonResponse
    {
        $this->authorize('update', $recordStatus);

        $recordStatus->update($request->validated());

        return response()->json(['data' => new RecordStatusResource($recordStatus->fresh())]);
    }

    /**
     * DELETE /api/v1/record-statuses/{id}
     */
    public function destroy(RecordStatus $recordStatus): Response
    {
        $this->authorize('delete', $recordStatus);

        $recordStatus->delete();

        return response()->noContent();
    }
}
