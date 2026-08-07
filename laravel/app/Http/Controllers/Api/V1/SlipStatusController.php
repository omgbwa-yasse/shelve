<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SlipStatus\StoreSlipStatusRequest;
use App\Http\Requests\Api\V1\SlipStatus\UpdateSlipStatusRequest;
use App\Http\Resources\Api\V1\SlipStatusResource;
use App\Models\SlipStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D04 — relu et validé le 2026-08-04 contre `SlipStatusController` et le schéma.
 *
 * Les statuts de bordereau sont un référentiel global (pas d'organisation) : motif
 * D01. Le destroy refuse 409 tant que le statut est affecté à des bordereaux (le
 * contrôleur Blade appelait une relation `transferrings()` inexistante — corrigé avec
 * la relation réelle `Slips()`).
 */
class SlipStatusController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['slips'];

    /**
     * GET /api/v1/slip-statuses
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SlipStatus::class);

        $query = SlipStatus::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, SlipStatusResource::class));
    }

    /**
     * GET /api/v1/slip-statuses/{id}
     */
    public function show(SlipStatus $slipStatus): JsonResponse
    {
        $this->authorize('view', $slipStatus);

        return response()->json(['data' => new SlipStatusResource($slipStatus)]);
    }

    /**
     * POST /api/v1/slip-statuses
     */
    public function store(StoreSlipStatusRequest $request): JsonResponse
    {
        $this->authorize('create', SlipStatus::class);

        $slipStatus = SlipStatus::create($request->validated());

        return response()->json(
            ['data' => new SlipStatusResource($slipStatus)],
            201,
            ['Location' => "/api/v1/slip-statuses/{$slipStatus->id}"]
        );
    }

    /**
     * PATCH /api/v1/slip-statuses/{id}
     */
    public function update(UpdateSlipStatusRequest $request, SlipStatus $slipStatus): JsonResponse
    {
        $this->authorize('update', $slipStatus);

        $slipStatus->update($request->validated());

        return response()->json(['data' => new SlipStatusResource($slipStatus->fresh())]);
    }

    /**
     * DELETE /api/v1/slip-statuses/{id}
     */
    public function destroy(SlipStatus $slipStatus): Response
    {
        $this->authorize('delete', $slipStatus);

        // Comme en Blade : refus si le statut est affecté à des bordereaux.
        if ($slipStatus->Slips()->exists()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit', 'status' => 409, 'detail' => 'Ce statut est affecté à un ou plusieurs bordereaux.'],
                409
            );
        }

        $slipStatus->delete();

        return response()->noContent();
    }
}
