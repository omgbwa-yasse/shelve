<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\SwitchOrganisationRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentification de l'API v1 (agents, guard `web`).
 *
 * Contrat : contracts/CONVENTIONS.md §6.
 *
 * Les tokens émis ici sont des tokens Sanctum stockés dans `personal_access_tokens`,
 * hachés en SHA-256. C'est ce choix qui permettra à Spring Boot de valider les MÊMES
 * tokens en phase 3, et donc de basculer domaine par domaine sans déconnecter les
 * utilisateurs (mesure du risque R21).
 *
 * Le back-office Blade continue d'utiliser la session `web` : les deux mécanismes
 * coexistent pendant toute la migration.
 */
class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        // Réponse et coût non différenciés entre « email inconnu » et « mot de passe
        // faux » : les distinguer permettrait d'énumérer les comptes (CONVENTIONS §6).
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken($request->deviceName())->plainTextToken;

        $user->load(['organisation', 'organisations', 'roles']);

        return response()->json([
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
                'permissions' => $this->effectivePermissions($user),
            ],
        ]);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['organisation', 'organisations', 'roles']);

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'permissions' => $this->effectivePermissions($user),
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/logout — révoque le token courant uniquement.
     */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/auth/logout-all — révoque tous les tokens de l'utilisateur.
     */
    public function logoutAll(Request $request): Response
    {
        $request->user()->tokens()->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/auth/switch-organisation
     *
     * Remplace ce que faisait la session côté Blade. L'organisation demandée doit
     * faire partie de celles auxquelles l'utilisateur est rattaché — sinon 403, sans
     * quoi n'importe qui pourrait se placer dans le contexte d'une autre organisation
     * et lire ses données (risque R03).
     */
    public function switchOrganisation(SwitchOrganisationRequest $request): JsonResponse
    {
        $user = $request->user();
        $organisationId = (int) $request->input('organisation_id');

        $allowed = $user->isSuperAdmin()
            || $user->organisations()->whereKey($organisationId)->exists();

        if (!$allowed) {
            return response()->json([
                'type' => 'https://shelve.local/errors/forbidden',
                'title' => "Vous n'êtes pas rattaché à cette organisation.",
                'status' => 403,
                'instance' => $request->path(),
            ], 403, ['Content-Type' => 'application/problem+json']);
        }

        $user->current_organisation_id = $organisationId;
        $user->save();

        $user->load(['organisation', 'organisations', 'roles']);

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'permissions' => $this->effectivePermissions($user),
            ],
        ]);
    }

    /**
     * Permissions effectives : permissions directes (`user_permissions`) réunies aux
     * permissions héritées des rôles (`user_roles` → `role_permissions`).
     *
     * Reproduit la logique de User::hasPermissionTo(), mais en une seule passe : le
     * client a besoin de la liste complète pour piloter son affichage, pas d'un test
     * unitaire par permission.
     */
    private function effectivePermissions(User $user): array
    {
        return $user->effectivePermissionNames();
    }
}
