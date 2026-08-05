<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Slip\StoreSlipRequest;
use App\Http\Requests\Api\V1\Slip\UpdateSlipRequest;
use App\Http\Resources\Api\V1\SlipResource;
use App\Models\Slip;
use App\Models\SlipStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D04 — relu et validé le 2026-08-04 contre `SlipController` et le schéma.
 *
 * Les bordereaux sont **org-scopés** (double organisation émetteur/bénéficiaire,
 * `HasDualOrganisation`) : l'index n'expose que les bordereaux impliquant l'organisation
 * courante, et une ressource hors périmètre répond 404 (motif D03, R03).
 * `officer_id` / `officer_organisation_id` sont posés depuis l'agent authentifié.
 *
 * TODO (actions métier complexes, non portées en phase 1) :
 *  - `storetransfert` : création d'un bordereau + copies des documents et pièces jointes
 *    d'un ensemble de `RecordPhysical` sélectionnés (multi-étapes).
 *  - `integrate` : conversion des `SlipRecord` en `RecordPhysical` (domaine D02, non porté).
 *  - `mailArchiving` : création d'un bordereau à partir de contenants de courriers.
 *  - `export` / `import` / `print` : exports Excel/EAD/SEDA et PDF via services dédiés.
 */
class SlipController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'slip_status_id', 'is_received', 'is_approved', 'is_integrated', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'slip_status_id', 'is_received', 'is_approved', 'is_integrated', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['officer', 'officerOrganisation', 'user', 'userOrganisation', 'slipStatus', 'records', 'receivedAgent', 'approvedAgent', 'integratedAgent'];

    /**
     * GET /api/v1/slips
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Slip::class);

        $query = Slip::forOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, SlipResource::class));
    }

    /**
     * GET /api/v1/slips/{id}
     */
    public function show(Slip $slip): JsonResponse
    {
        $this->authorize('view', $slip);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);

        return response()->json(['data' => new SlipResource($slip)]);
    }

    /**
     * POST /api/v1/slips
     */
    public function store(StoreSlipRequest $request): JsonResponse
    {
        $this->authorize('create', Slip::class);

        // Comme en Blade : statut par défaut « Projects » (repli sur l'id 1).
        $defaultStatus = SlipStatus::where('name', 'Projects')->first();

        $slip = Slip::create($request->validated() + [
            'officer_id' => Auth::id(),
            'officer_organisation_id' => Auth::user()->current_organisation_id,
            'slip_status_id' => $defaultStatus?->id ?? 1,
        ]);

        return response()->json(
            ['data' => new SlipResource($slip)],
            201,
            ['Location' => "/api/v1/slips/{$slip->id}"]
        );
    }

    /**
     * PATCH /api/v1/slips/{id}
     */
    public function update(UpdateSlipRequest $request, Slip $slip): JsonResponse
    {
        $this->authorize('update', $slip);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);

        $slip->update($request->validated());

        return response()->json(['data' => new SlipResource($slip->fresh())]);
    }

    /**
     * DELETE /api/v1/slips/{id}
     */
    public function destroy(Slip $slip): Response
    {
        $this->authorize('delete', $slip);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);

        // Comme en Blade : refus si le bordereau contient encore des documents.
        if ($slip->records()->exists()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit', 'status' => 409, 'detail' => 'Le bordereau contient des documents. Veuillez le vider avant de le supprimer.'],
                409
            );
        }

        $slip->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/slips/{slip}/receive — réception du bordereau.
     */
    public function receive(Slip $slip): JsonResponse
    {
        $this->authorize('update', $slip);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);

        $slip->update([
            'is_received' => true,
            'received_by' => Auth::id(),
            'received_date' => now(),
        ]);

        return response()->json(['data' => new SlipResource($slip->fresh())]);
    }

    /**
     * POST /api/v1/slips/{slip}/approve — approbation d'un bordereau reçu.
     */
    public function approve(Slip $slip): JsonResponse
    {
        $this->authorize('update', $slip);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);

        // Comme en Blade : on n'approuve qu'un bordereau reçu avec date de réception.
        if (!$slip->is_received || empty($slip->received_date)) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit', 'status' => 409, 'detail' => 'Le bordereau doit être reçu (avec date de réception) avant d\'être approuvé.'],
                409
            );
        }

        $slip->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_date' => now(),
        ]);

        return response()->json(['data' => new SlipResource($slip->fresh())]);
    }
}
