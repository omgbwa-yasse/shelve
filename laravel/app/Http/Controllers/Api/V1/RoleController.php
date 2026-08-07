<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Role\StoreRoleRequest;
use App\Http\Requests\Api\V1\Role\UpdateRoleRequest;
use App\Http\Resources\Api\V1\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D09 — rôles, relu le 2026-08-05 contre `RoleController` et le schéma.
 *
 * Référentiel global. La table `roles` porte `description` et `guard_name`
 * (NOT NULL DEFAULT 'web'), PAS `display_name` (défaut corrigé au passage dans
 * le modèle — ne pas réintroduire). Le rattachement d'un rôle à ses permissions
 * (RolePermissionController, matrice de synchronisation) n'est pas une ressource
 * REST : voir abandon D09.
 */
class RoleController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'guard_name', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'guard_name', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['permissions', 'users'];

    /**
     * GET /api/v1/roles
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RoleResource::class));
    }

    /**
     * GET /api/v1/roles/{id}
     */
    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        return response()->json(['data' => new RoleResource($role)]);
    }

    /**
     * POST /api/v1/roles
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        // `guard_name` a un défaut en base ('web') : l'agent peut l'omettre.
        $role = Role::create($request->validated() + ['guard_name' => 'web']);

        return response()->json(
            ['data' => new RoleResource($role)],
            201,
            ['Location' => "/api/v1/roles/{$role->id}"]
        );
    }

    /**
     * PATCH /api/v1/roles/{id}
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $this->authorize('update', $role);

        $role->update($request->validated());

        return response()->json(['data' => new RoleResource($role->fresh())]);
    }

    /**
     * DELETE /api/v1/roles/{id}
     */
    public function destroy(Role $role): Response
    {
        $this->authorize('delete', $role);

        $role->delete();

        return response()->noContent();
    }
}
