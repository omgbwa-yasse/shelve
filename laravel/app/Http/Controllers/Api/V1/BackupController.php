<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Backup\StoreBackupRequest;
use App\Http\Requests\Api\V1\Backup\UpdateBackupRequest;
use App\Http\Resources\Api\V1\BackupResource;
use App\Models\Backup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D16 — sauvegardes (référentiel global, ressource d'exploitation). Porté le 2026-08-04.
 *
 * `user_id` est posé depuis l'agent authentifié (le Blade utilisait `auth()->id()`).
 * Les champs techniques `date_time`, `size`, `backup_file`, `path` sont gérés serveur.
 *
 * ⚠️ TODO (E2, phase 3) : la génération réelle d'une sauvegarde (mysqldump + archive
 * ZIP) dans `BackupController::store()` du Blade n'est PAS portée ici — c'est un job
 * d'exploitation binaire. L'endpoint `store` crée l'enregistrement CRUD, les valeurs
 * techniques étant posées par défaut côté serveur.
 */
class BackupController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'type', 'status', 'user_id', 'date_time', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'type', 'status', 'user_id', 'date_time', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['user', 'backupFiles', 'backupPlannings'];

    /**
     * GET /api/v1/backups
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Backup::class);

        $query = Backup::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, BackupResource::class));
    }

    /**
     * GET /api/v1/backups/{id}
     */
    public function show(Backup $backup): JsonResponse
    {
        $this->authorize('view', $backup);

        return response()->json(['data' => new BackupResource($backup)]);
    }

    /**
     * POST /api/v1/backups
     */
    public function store(StoreBackupRequest $request): JsonResponse
    {
        $this->authorize('create', Backup::class);

        // TODO (E2, phase 3) : génération réelle de la sauvegarde (mysqldump + ZIP).
        $backup = Backup::create($request->validated() + [
            'date_time' => now(),
            'user_id' => Auth::id(),
            'size' => 0,
            'backup_file' => '',
            'path' => '',
        ]);

        return response()->json(
            ['data' => new BackupResource($backup)],
            201,
            ['Location' => "/api/v1/backups/{$backup->id}"]
        );
    }

    /**
     * PATCH /api/v1/backups/{id}
     */
    public function update(UpdateBackupRequest $request, Backup $backup): JsonResponse
    {
        $this->authorize('update', $backup);

        $backup->update($request->validated());

        return response()->json(['data' => new BackupResource($backup->fresh())]);
    }

    /**
     * DELETE /api/v1/backups/{id}
     */
    public function destroy(Backup $backup): Response
    {
        $this->authorize('delete', $backup);

        // TODO (E2, phase 3) : suppression du fichier de sauvegarde sur le disque,
        // comme le fait le Blade (`Storage::delete($backup->backup_file)`).
        $backup->delete();

        return response()->noContent();
    }
}
