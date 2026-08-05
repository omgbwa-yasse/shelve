<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\StoreUserRequest;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D09 — agents (`users`), relu le 2026-08-05 contre `UserController` et le schéma.
 *
 * Les utilisateurs sont les AGENTS connectés : référentiel global (pas
 * d'isolation par organisation). `password` n'est JAMAIS exposé par la Resource
 * et est accepté en écriture uniquement via le cast `hashed` du modèle — le
 * hachage reste côté serveur. `users.birthday` est NOT NULL sans défaut : requis
 * à la création. `current_organisation_id` reste libre (l'agent le bascule via
 * `auth/switch-organisation`).
 */
class UserController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'surname', 'birthday', 'email', 'email_verified_at', 'current_organisation_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'surname', 'birthday', 'email', 'email_verified_at', 'current_organisation_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['organisation', 'organisations', 'roles'];

    /**
     * GET /api/v1/users
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, UserResource::class));
    }

    /**
     * GET /api/v1/users/{id}
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json(['data' => new UserResource($user)]);
    }

    /**
     * POST /api/v1/users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        // `password` est haché par le cast `hashed` du modèle.
        $user = User::create($request->validated());

        return response()->json(
            ['data' => new UserResource($user)],
            201,
            ['Location' => "/api/v1/users/{$user->id}"]
        );
    }

    /**
     * PATCH /api/v1/users/{id}
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

        return response()->json(['data' => new UserResource($user->fresh())]);
    }

    /**
     * DELETE /api/v1/users/{id}
     */
    public function destroy(User $user): Response
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->noContent();
    }
}
