<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\KpiMeasurement\StoreKpiMeasurementRequest;
use App\Http\Resources\Api\V1\KpiMeasurementResource;
use App\Models\Kpi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D17 — Historique des mesures d'un KPI. Voir
 * `evolution/PROJECT-OKR-KPI-PLAN.md`, §3 (nécessaire pour un graphique de
 * tendance — un KPI sans historique perd sa dimension temporelle).
 */
class KpiMeasurementController extends Controller
{
    /**
     * GET /api/v1/kpis/{kpi}/measurements?from=&to=
     */
    public function index(Request $request, Kpi $kpi): JsonResponse
    {
        $this->authorize('view', $kpi);

        $kpi = Kpi::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($kpi->id);

        $query = $kpi->measurements();

        if ($request->filled('from')) {
            $query->where('measured_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('measured_at', '<=', $request->input('to'));
        }

        return response()->json(['data' => KpiMeasurementResource::collection($query->get())]);
    }

    /**
     * POST /api/v1/kpis/{kpi}/measurements
     */
    public function store(StoreKpiMeasurementRequest $request, Kpi $kpi): JsonResponse
    {
        $this->authorize('recordMeasurement', $kpi);

        $kpi = Kpi::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($kpi->id);

        $measurement = $kpi->measurements()->create($request->validated() + [
            'measured_at' => $request->input('measured_at', now()->toDateString()),
            'recorded_by' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new KpiMeasurementResource($measurement)],
            201,
            ['Location' => "/api/v1/kpis/{$kpi->id}/measurements/{$measurement->id}"]
        );
    }
}
