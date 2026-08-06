<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Kpi\StoreKpiRequest;
use App\Http\Requests\Api\V1\Kpi\UpdateKpiRequest;
use App\Http\Resources\Api\V1\KpiResource;
use App\Models\Kpi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D17 — KPI. Voir `evolution/PROJECT-OKR-KPI-PLAN.md`, §3. L'historique des
 * mesures vit dans `kpi_measurements` (voir KpiMeasurementController).
 */
class KpiController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'attachable_type', 'attachable_id', 'organisation_id', 'owner_id', 'frequency'];
    private const SORTABLE = ['id', 'code', 'name', 'created_at'];
    private const INCLUDABLE = ['owner', 'creator', 'updater', 'attachable', 'measurements'];

    /**
     * GET /api/v1/kpis
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Kpi::class);

        $query = Kpi::byOrganisation(Auth::user()->current_organisation_id)
            ->with(['measurements' => fn ($q) => $q->limit(2)]);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->orderByDesc('created_at')->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, KpiResource::class));
    }

    /**
     * GET /api/v1/kpis/{id}
     */
    public function show(Kpi $kpi): JsonResponse
    {
        $this->authorize('view', $kpi);

        $kpi = Kpi::byOrganisation(Auth::user()->current_organisation_id)
            ->with(['owner', 'attachable', 'measurements'])
            ->findOrFail($kpi->id);

        return response()->json(['data' => new KpiResource($kpi)]);
    }

    /**
     * POST /api/v1/kpis
     */
    public function store(StoreKpiRequest $request): JsonResponse
    {
        $this->authorize('create', Kpi::class);

        $data = $request->validated();
        $data['attachable_type'] = Kpi::resolveAttachableAlias($data['attachable_type']);

        $kpi = Kpi::create($data + [
            'organisation_id' => Auth::user()->current_organisation_id,
            'created_by' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new KpiResource($kpi->load('attachable'))],
            201,
            ['Location' => "/api/v1/kpis/{$kpi->id}"]
        );
    }

    /**
     * PATCH /api/v1/kpis/{id}
     */
    public function update(UpdateKpiRequest $request, Kpi $kpi): JsonResponse
    {
        $this->authorize('update', $kpi);

        $kpi = Kpi::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($kpi->id);

        $data = $request->validated();

        if (isset($data['attachable_type'])) {
            $data['attachable_type'] = Kpi::resolveAttachableAlias($data['attachable_type']);
        }

        $kpi->update($data + ['updated_by' => Auth::id()]);

        return response()->json(['data' => new KpiResource($kpi->fresh('attachable'))]);
    }

    /**
     * DELETE /api/v1/kpis/{id}
     */
    public function destroy(Kpi $kpi): Response
    {
        $this->authorize('delete', $kpi);

        $kpi = Kpi::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($kpi->id);

        $kpi->delete();

        return response()->noContent();
    }
}
