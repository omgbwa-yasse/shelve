<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordReactivation\UpdateRecordReactivationRequest;
use App\Http\Resources\Api\V1\RecordReactivationResource;
use App\Models\RecordReactivation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D02 — demandes de réactivation, relu et validé le 2026-08-04 contre
 * `RecordReactivationController`.
 *
 * Ressource **org-scopée** par `organisation_id` (motif D03) : une demande d'une
 * autre organisation répond 404. La création passe par l'action `reactivation` de la
 * ressource `records` (`POST /records/{record}/reactivations`) : `previous_status_id`,
 * `requested_by` et `requested_date` y sont posés depuis la notice et l'agent.
 *
 * Policy : `RecordReactivationPolicy` (préfixe `record_reactivation_*`, `update`
 * couvre approbation et rejet — permission `record_reactivation_approve`).
 */
class RecordReactivationController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'record_id', 'organisation_id', 'previous_status_id', 'is_approved', 'requested_by', 'approved_by', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'record_id', 'organisation_id', 'previous_status_id', 'is_approved', 'requested_by', 'approved_by', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['record', 'previousStatus', 'requestedBy', 'approvedBy'];

    /**
     * GET /api/v1/record-reactivations
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecordReactivation::class);

        $query = RecordReactivation::inOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordReactivationResource::class));
    }

    /**
     * POST /api/v1/record-reactivations/{reactivation}/approve
     */
    public function approve(RecordReactivation $reactivation): JsonResponse
    {
        $this->authorize('update', $reactivation);

        $reactivation = RecordReactivation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reactivation->id);

        if ($reactivation->is_approved) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit', 'status' => 422, 'detail' => 'Cette demande a déjà été approuvée.', 'errors' => ['is_approved' => ['Cette demande a déjà été approuvée.']]],
                422
            );
        }

        $reactivation->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_date' => now(),
        ]);

        // Comme en Blade : la notice retrouve son statut antérieur.
        if ($reactivation->previous_status_id) {
            $reactivation->record()->update(['status_id' => $reactivation->previous_status_id]);
        }

        return response()->json(['data' => new RecordReactivationResource($reactivation->fresh(['record', 'previousStatus', 'requestedBy', 'approvedBy']))]);
    }

    /**
     * POST /api/v1/record-reactivations/{reactivation}/reject
     */
    public function reject(UpdateRecordReactivationRequest $request, RecordReactivation $reactivation): JsonResponse
    {
        $this->authorize('update', $reactivation);

        $reactivation = RecordReactivation::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($reactivation->id);

        $reactivation->update(['rejection_reason' => $request->validated()['reason']]);

        return response()->json(['data' => new RecordReactivationResource($reactivation->fresh())]);
    }
}
