<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserRole\StoreUserRoleRequest;
use App\Http\Requests\Api\V1\UserRole\UpdateUserRoleRequest;
use App\Http\Resources\Api\V1\UserRoleResource;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D09 — rattachements agent→rôle (`user_roles`), relu le 2026-08-05.
 *
 * Pivot GLOBAL (pas d'organisation) : le rôle d'un agent ne dépend pas de
 * l'organisation courante. Le contrôleur Blade `UserRoleController` était cassé
 * (classe `UserRole` inexistante, `where($id)` invalide) : le modèle a été créé
 * et le CRUD reconstruit à partir du schéma (table avec `id` auto-incrémenté).
 */
class UserRoleController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'user_id', 'role_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'user_id', 'role_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['user', 'role'];

    /**
     * GET /api/v1/user-roles
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserRole::class);

        $query = UserRole::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, UserRoleResource::class));
    }

    /**
     * GET /api/v1/user-roles/{id}
     */
    public function show(UserRole $userRole): JsonResponse
    {
        $this->authorize('view', $userRole);

        return response()->json(['data' => new UserRoleResource($userRole)]);
    }

    /**
     * POST /api/v1/user-roles
     */
    public function store(StoreUserRoleRequest $request): JsonResponse
    {
        $this->authorize('create', UserRole::class);

        $userRole = UserRole::create($request->validated());

        return response()->json(
            ['data' => new UserRoleResource($userRole)],
            201,
            ['Location' => "/api/v1/user-roles/{$userRole->id}"]
        );
    }

    /**
     * PATCH /api/v1/user-roles/{id}
     */
    public function update(UpdateUserRoleRequest $request, UserRole $userRole): JsonResponse
    {
        $this->authorize('update', $userRole);

        $userRole->update($request->validated());

        return response()->json(['data' => new UserRoleResource($userRole->fresh())]);
    }

    /**
     * DELETE /api/v1/user-roles/{id}
     */
    public function destroy(UserRole $userRole): Response
    {
        $this->authorize('delete', $userRole);

        $userRole->delete();

        return response()->noContent();
    }
}
