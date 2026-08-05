<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RecordLifecycleResource;
use App\Models\Record;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D07 — rapports de cycle de vie, portés le 2026-08-04 depuis `LifeCycleController`.
 *
 * Six listes en lecture seule, bornées à l'organisation courante. La logique SQL de
 * calcul de la date de référence (`COALESCE(end_date, date_exact)` + conversions de
 * format) est reprise du Blade ; la phase suivante (phase 3) pourra la porter côté
 * colonnes calculées. Les actions d'élimination/transfert elles-mêmes relèvent du
 * workflow de déclassement (voir DeclassementListController) : TODO pour tout
 * portage mutateur.
 */
class LifeCycleController extends Controller
{
    use HandlesApiQueries;

    private function referenceDateExpression(): string
    {
        return 'COALESCE(records.end_date, records.date_exact)';
    }

    private function retentionExpiredCondition(): string
    {
        return 'DATEDIFF(NOW(), ' . $this->referenceDateExpression() . ') > retentions.duration * 365';
    }

    private function retentionActiveCondition(): string
    {
        return 'DATEDIFF(NOW(), ' . $this->referenceDateExpression() . ') <= retentions.duration * 365';
    }

    private function communicabilityExpiredCondition(): string
    {
        return 'DATEDIFF(NOW(), ' . $this->referenceDateExpression() . ') > communicabilities.duration * 365';
    }

    private function retentionBaseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Record::join('activities', 'records.activity_id', '=', 'activities.id')
            ->join('retention_activity', 'activities.id', '=', 'retention_activity.activity_id')
            ->join('retentions', 'retention_activity.retention_id', '=', 'retentions.id')
            ->join('sorts', 'retentions.sort_id', '=', 'sorts.id')
            ->where('records.organisation_id', Auth::user()->current_organisation_id)
            ->select('records.*')
            ->orderByRaw($this->referenceDateExpression() . ' DESC');
    }

    private function respond($query, Request $request): JsonResponse
    {
        $page = $query->with(['activity', 'status', 'level', 'creator'])
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordLifecycleResource::class));
    }

    /**
     * GET /api/v1/transferrings/lifecycle/retain
     * Sort = C, période de rétention active.
     */
    public function recordToRetain(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        return $this->respond(
            $this->retentionBaseQuery()
                ->where('sorts.code', 'C')
                ->whereRaw($this->retentionActiveCondition()),
            $request
        );
    }

    /**
     * GET /api/v1/transferrings/lifecycle/keep
     * Identique à `recordToRetain` (contexte « en attente de conservation »).
     */
    public function recordToKeep(Request $request): JsonResponse
    {
        return $this->recordToRetain($request);
    }

    /**
     * GET /api/v1/transferrings/lifecycle/transfer
     * Communicabilité écoulée.
     */
    public function recordToTransfer(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        $query = Record::join('activities', 'records.activity_id', '=', 'activities.id')
            ->join('communicabilities', 'activities.communicability_id', '=', 'communicabilities.id')
            ->where('records.organisation_id', Auth::user()->current_organisation_id)
            ->whereRaw($this->communicabilityExpiredCondition())
            ->select('records.*')
            ->orderByRaw($this->referenceDateExpression() . ' DESC');

        return $this->respond($query, $request);
    }

    /**
     * GET /api/v1/transferrings/lifecycle/sort
     * Sort = T, durée de rétention écoulée.
     */
    public function recordToSort(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        return $this->respond(
            $this->retentionBaseQuery()
                ->where('sorts.code', 'T')
                ->whereRaw($this->retentionExpiredCondition()),
            $request
        );
    }

    /**
     * GET /api/v1/transferrings/lifecycle/store
     * Sort = C, durée de rétention écoulée.
     */
    public function recordToStore(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        return $this->respond(
            $this->retentionBaseQuery()
                ->where('sorts.code', 'C')
                ->whereRaw($this->retentionExpiredCondition()),
            $request
        );
    }

    /**
     * GET /api/v1/transferrings/lifecycle/eliminate
     * Sort = E, durée de rétention écoulée.
     */
    public function recordToEliminate(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        return $this->respond(
            $this->retentionBaseQuery()
                ->where('sorts.code', 'E')
                ->whereRaw($this->retentionExpiredCondition()),
            $request
        );
    }
}
