<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BackupPlanning\StoreBackupPlanningRequest;
use App\Http\Requests\Api\V1\BackupPlanning\UpdateBackupPlanningRequest;
use App\Http\Resources\Api\V1\BackupPlanningResource;
use App\Models\BackupPlanning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D16 — planification d'une sauvegarde (référentiel global). Porté le 2026-08-04.
 *
 * Le Blade route ces ressources sous `/settings/backups/{backup}/plannings` : portage
 * en ressource plate `/backup-plannings`, parenté conservée via `backup_id`.
 */
class BackupPlanningController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'backup_id', 'frequence', 'week_day', 'month_day', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'backup_id', 'frequence', 'week_day', 'month_day', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['backup'];

    /**
     * GET /api/v1/backup-plannings
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BackupPlanning::class);

        $query = BackupPlanning::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, BackupPlanningResource::class));
    }

    /**
     * GET /api/v1/backup-plannings/{id}
     */
    public function show(BackupPlanning $backupPlanning): JsonResponse
    {
        $this->authorize('view', $backupPlanning);

        return response()->json(['data' => new BackupPlanningResource($backupPlanning)]);
    }

    /**
     * POST /api/v1/backup-plannings
     */
    public function store(StoreBackupPlanningRequest $request): JsonResponse
    {
        $this->authorize('create', BackupPlanning::class);

        $backupPlanning = BackupPlanning::create($request->validated());

        return response()->json(
            ['data' => new BackupPlanningResource($backupPlanning)],
            201,
            ['Location' => "/api/v1/backup-plannings/{$backupPlanning->id}"]
        );
    }

    /**
     * PATCH /api/v1/backup-plannings/{id}
     */
    public function update(UpdateBackupPlanningRequest $request, BackupPlanning $backupPlanning): JsonResponse
    {
        $this->authorize('update', $backupPlanning);

        $backupPlanning->update($request->validated());

        return response()->json(['data' => new BackupPlanningResource($backupPlanning->fresh())]);
    }

    /**
     * DELETE /api/v1/backup-plannings/{id}
     */
    public function destroy(BackupPlanning $backupPlanning): Response
    {
        $this->authorize('delete', $backupPlanning);

        $backupPlanning->delete();

        return response()->noContent();
    }
}
