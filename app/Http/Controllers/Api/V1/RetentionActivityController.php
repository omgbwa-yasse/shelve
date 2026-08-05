<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RetentionActivity\StoreRetentionActivityRequest;
use App\Http\Resources\Api\V1\RetentionActivityResource;
use App\Models\RetentionActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D07 — liaison activité ↔ durée de conservation (pivot `retention_activity`).
 *
 * Le pivot n'a PAS de colonne `id` (clé composite retention_id + activity_id) :
 * `show`/`update` n'ont pas de sens en REST et la route `destroy` est portée par la
 * paire {retention}/{activity}. C'est l'écart assumé au gabarit apiResource (voir
 * `routes/api/D07.php`). Le contrôleur Blade ne faisait que `index`/`store` sur ce pivot.
 *
 * Référentiel global (motif D01) : `retention_activity` relie deux référentiels globaux.
 */
class RetentionActivityController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['retention_id', 'activity_id'];
    private const SORTABLE = ['retention_id', 'activity_id'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/retention-activities
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RetentionActivity::class);

        $query = RetentionActivity::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'retention_id');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RetentionActivityResource::class));
    }

    /**
     * POST /api/v1/retention-activities
     *
     * Rattache une durée de conservation à une activité. `firstOrCreate` évite la
     * duplication de lignes de pivot que l'`attach` du Blade tolérait.
     */
    public function store(StoreRetentionActivityRequest $request): JsonResponse
    {
        $this->authorize('create', RetentionActivity::class);

        $retentionActivity = RetentionActivity::firstOrCreate($request->validated());

        return response()->json(
            ['data' => new RetentionActivityResource($retentionActivity)],
            201,
            ['Location' => "/api/v1/retention-activities/{$retentionActivity->retention_id}/{$retentionActivity->activity_id}"]
        );
    }

    /**
     * DELETE /api/v1/retention-activities/{retention}/{activity}
     *
     * Retire la liaison. Le pivot est résolu par sa clé composite (pas d'id en base),
     * et la suppression passe par le Query Builder (l'instance `delete()` d'Eloquent
     * s'appuierait sur la clé primaire `id`, absente de la table) : 404 sinon.
     */
    public function destroy(int $retention, int $activity): Response
    {
        $pivot = RetentionActivity::query()
            ->where('retention_id', $retention)
            ->where('activity_id', $activity)
            ->firstOrFail();

        $this->authorize('delete', $pivot);

        RetentionActivity::query()
            ->where('retention_id', $retention)
            ->where('activity_id', $activity)
            ->delete();

        return response()->noContent();
    }
}
