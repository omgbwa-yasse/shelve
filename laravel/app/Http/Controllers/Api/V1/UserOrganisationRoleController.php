<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserOrganisationRole\StoreUserOrganisationRoleRequest;
use App\Http\Requests\Api\V1\UserOrganisationRole\UpdateUserOrganisationRoleRequest;
use App\Http\Resources\Api\V1\UserOrganisationRoleResource;
use App\Models\Organisation;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D09 — rattachements agent→organisation→rôle (`user_organisation_role`), relu
 * le 2026-08-05. C'est le pivot le plus délicat du domaine.
 *
 * La table a une clé primaire COMPOSITE (`user_id`, `organisation_id`) et PAS de
 * colonne `id` : un `apiResource` classique (`{user_organisation_role}`) ne peut
 * pas fonctionner (binding sur `id` impossible). Les routes show/update/destroy
 * sont donc déclarées avec la paire (`{user}`, `{organisation}`), et le modèle
 * est résolu par ses deux clés. Conséquence Eloquent : `$model->update()`/
 * `->delete()`/`->fresh()` (qui identifient la ligne via `whereKey('id')`)
 * généreraient du SQL invalide — toutes les mutations passent par le query
 * builder avec les deux clés.
 *
 * `role_id` ET `creator_id` sont NOT NULL : `creator_id` est posé depuis l'agent
 * authentifié, jamais accepté du client.
 *
 * R03 — isolation : la table porte `organisation_id`, donc le pivot est
 * org-scopé. La Policy (`canView`/`canUpdate`/`canDelete` → `access-in-
 * organisation`) renvoie 404 pour tout pivot d'une autre organisation, et
 * l'index n'expose que l'organisation courante.
 */
class UserOrganisationRoleController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['user_id', 'organisation_id', 'role_id', 'creator_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['user_id', 'organisation_id', 'role_id', 'creator_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['user', 'organisation', 'role', 'creator'];

    /**
     * GET /api/v1/user-organisation-roles
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserOrganisationRole::class);

        $query = UserOrganisationRole::where('organisation_id', Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        // `applySorting()` trie par `id` par défaut : cette table n'en a pas (clé
        // composite user_id+organisation_id). Sans ce troisième argument, tout appel
        // sans `?sort=` explicite renvoyait un 500 (SQLSTATE 42S22 « Unknown column
        // 'id' »).
        $this->applySorting($query, $request, self::SORTABLE, 'created_at');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, UserOrganisationRoleResource::class));
    }

    /**
     * GET /api/v1/user-organisation-roles/{user}/{organisation}
     */
    public function show(User $user, Organisation $organisation): JsonResponse
    {
        $pivot = $this->resolve($user, $organisation);

        // R03 : un pivot d'une autre organisation répond 404 via la Policy.
        $this->authorize('view', $pivot);

        return response()->json(['data' => new UserOrganisationRoleResource($pivot)]);
    }

    /**
     * POST /api/v1/user-organisation-roles
     */
    public function store(StoreUserOrganisationRoleRequest $request): JsonResponse
    {
        $this->authorize('create', UserOrganisationRole::class);

        $data = $request->validated();

        // R03 : hors superadmin, on ne rattache que dans son organisation courante.
        if (!$this->canManageOrganisation((int) $data['organisation_id'])) {
            return $this->notFound();
        }

        $data['creator_id'] = Auth::id();

        // Comme en Blade : idempotent sur la paire (user_id, organisation_id).
        $pivot = UserOrganisationRole::firstOrCreate(
            ['user_id' => $data['user_id'], 'organisation_id' => $data['organisation_id']],
            $data
        );

        $created = $pivot->wasRecentlyCreated;

        return response()->json(
            ['data' => new UserOrganisationRoleResource($this->resolveByIds($data['user_id'], $data['organisation_id']))],
            $created ? 201 : 200,
            $created ? ['Location' => "/api/v1/user-organisation-roles/{$data['user_id']}/{$data['organisation_id']}"] : []
        );
    }

    /**
     * PATCH /api/v1/user-organisation-roles/{user}/{organisation}
     */
    public function update(UpdateUserOrganisationRoleRequest $request, User $user, Organisation $organisation): JsonResponse
    {
        $pivot = $this->resolve($user, $organisation);

        $this->authorize('update', $pivot);

        UserOrganisationRole::where('user_id', $user->id)
            ->where('organisation_id', $organisation->id)
            ->update($request->validated());

        return response()->json(['data' => new UserOrganisationRoleResource($this->resolve($user, $organisation))]);
    }

    /**
     * DELETE /api/v1/user-organisation-roles/{user}/{organisation}
     */
    public function destroy(User $user, Organisation $organisation): Response
    {
        $pivot = $this->resolve($user, $organisation);

        $this->authorize('delete', $pivot);

        UserOrganisationRole::where('user_id', $user->id)
            ->where('organisation_id', $organisation->id)
            ->delete();

        return response()->noContent();
    }

    /**
     * Résolution du pivot par sa clé primaire composite.
     */
    private function resolve(User $user, Organisation $organisation): UserOrganisationRole
    {
        return $this->resolveByIds($user->id, $organisation->id);
    }

    private function resolveByIds(int $userId, int $organisationId): UserOrganisationRole
    {
        return UserOrganisationRole::where('user_id', $userId)
            ->where('organisation_id', $organisationId)
            ->firstOrFail();
    }

    private function canManageOrganisation(int $organisationId): bool
    {
        return Auth::user()->isSuperAdmin()
            || Auth::user()->current_organisation_id === $organisationId;
    }

    private function notFound(): JsonResponse
    {
        return response()->json([
            'type' => 'https://shelve.local/errors/not-found',
            'title' => 'Ressource introuvable.',
            'status' => 404,
        ], 404);
    }
}
