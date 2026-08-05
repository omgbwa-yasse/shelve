<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BackupFile\StoreBackupFileRequest;
use App\Http\Requests\Api\V1\BackupFile\UpdateBackupFileRequest;
use App\Http\Resources\Api\V1\BackupFileResource;
use App\Models\BackupFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D16 — fichiers d'une sauvegarde (référentiel global). Porté le 2026-08-04.
 *
 * Le Blade route ces ressources sous `/settings/backups/{backup}/files` : le portage
 * API v1 les expose en ressource plate `/backup-files`, la parenté étant conservée
 * via `backup_id` (filtrable) — même motif que les référentiels D01/D03.
 */
class BackupFileController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'backup_id', 'size', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'backup_id', 'size', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['backup'];

    /**
     * GET /api/v1/backup-files
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BackupFile::class);

        $query = BackupFile::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, BackupFileResource::class));
    }

    /**
     * GET /api/v1/backup-files/{id}
     */
    public function show(BackupFile $backupFile): JsonResponse
    {
        $this->authorize('view', $backupFile);

        return response()->json(['data' => new BackupFileResource($backupFile)]);
    }

    /**
     * POST /api/v1/backup-files
     */
    public function store(StoreBackupFileRequest $request): JsonResponse
    {
        $this->authorize('create', BackupFile::class);

        $backupFile = BackupFile::create($request->validated());

        return response()->json(
            ['data' => new BackupFileResource($backupFile)],
            201,
            ['Location' => "/api/v1/backup-files/{$backupFile->id}"]
        );
    }

    /**
     * PATCH /api/v1/backup-files/{id}
     */
    public function update(UpdateBackupFileRequest $request, BackupFile $backupFile): JsonResponse
    {
        $this->authorize('update', $backupFile);

        $backupFile->update($request->validated());

        return response()->json(['data' => new BackupFileResource($backupFile->fresh())]);
    }

    /**
     * DELETE /api/v1/backup-files/{id}
     */
    public function destroy(BackupFile $backupFile): Response
    {
        $this->authorize('delete', $backupFile);

        $backupFile->delete();

        return response()->noContent();
    }
}
