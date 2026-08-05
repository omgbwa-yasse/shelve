<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Log\StoreLogRequest;
use App\Http\Requests\Api\V1\Log\UpdateLogRequest;
use App\Http\Resources\Api\V1\LogResource;
use App\Models\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D16 — journal d'audit (référentiel global). Porté le 2026-08-04.
 *
 * `user_id` est posé depuis l'agent authentifié ; `ip_address` et `user_agent` sont
 * prélevés sur la requête quand le client n'en fournit pas (le schéma les impose
 * NOT NULL, le Blade les déclarait nullables — les défauts serveur corrigent ce trou).
 */
class LogController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'user_id', 'action', 'ip_address', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'user_id', 'action', 'ip_address', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['user'];

    /**
     * GET /api/v1/logs
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Log::class);

        $query = Log::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, LogResource::class));
    }

    /**
     * GET /api/v1/logs/{id}
     */
    public function show(Log $log): JsonResponse
    {
        $this->authorize('view', $log);

        return response()->json(['data' => new LogResource($log)]);
    }

    /**
     * POST /api/v1/logs
     */
    public function store(StoreLogRequest $request): JsonResponse
    {
        $this->authorize('create', Log::class);

        $data = $request->validated();

        $log = Log::create([
            'user_id' => Auth::id(),
            'action' => $data['action'],
            'description' => $data['description'] ?? '',
            'ip_address' => $data['ip_address'] ?? $request->ip(),
            'user_agent' => $data['user_agent'] ?? (string) $request->userAgent(),
        ]);

        return response()->json(
            ['data' => new LogResource($log)],
            201,
            ['Location' => "/api/v1/logs/{$log->id}"]
        );
    }

    /**
     * PATCH /api/v1/logs/{id}
     */
    public function update(UpdateLogRequest $request, Log $log): JsonResponse
    {
        $this->authorize('update', $log);

        $log->update($request->validated());

        return response()->json(['data' => new LogResource($log->fresh())]);
    }

    /**
     * DELETE /api/v1/logs/{id}
     */
    public function destroy(Log $log): Response
    {
        $this->authorize('delete', $log);

        $log->delete();

        return response()->noContent();
    }
}
