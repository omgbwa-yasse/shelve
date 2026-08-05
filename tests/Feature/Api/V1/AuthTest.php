<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 1, étape 1.0.4 — socle d'authentification de l'API v1.
 *
 * Base : `shelve_test`, préparée par scripts/setup-test-db.sh.
 * On utilise DatabaseTransactions et non RefreshDatabase : les migrations ne
 * rejouent pas sur une base vierge (voir l'en-tête du script de préparation),
 * un rollback par test est donc à la fois nécessaire et suffisant.
 */
class AuthTest extends TestCase
{
    use DatabaseTransactions;

    private function makeOrganisation(string $code): Organisation
    {
        return Organisation::create([
            'code' => $code,
            'name' => "Organisation $code",
        ]);
    }

    private function makeUser(Organisation $org, string $email = 'agent@test.local'): User
    {
        return User::create([
            'name' => 'Agent',
            'surname' => 'De Test',
            'email' => $email,
            // `users.birthday` est NOT NULL sans valeur par défaut : toute création
            // d'agent doit la fournir. À reporter dans le FormRequest de D09.
            'birthday' => '1990-01-01',
            'password' => Hash::make('secret-123'),
            'current_organisation_id' => $org->id,
        ]);
    }

    public function test_login_retourne_un_token_et_le_profil(): void
    {
        $org = $this->makeOrganisation('T01');
        $this->makeUser($org);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@test.local',
            'password' => 'secret-123',
            'device_name' => 'phpunit',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'current_organisation_id', 'is_superadmin'],
                    'permissions',
                ],
            ])
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', 'agent@test.local');

        // Le mot de passe ne doit jamais transiter, même haché.
        $response->assertJsonMissingPath('data.user.password');
    }

    public function test_login_refuse_un_mauvais_mot_de_passe(): void
    {
        $org = $this->makeOrganisation('T02');
        $this->makeUser($org, 'agent2@test.local');

        $this->postJson('/api/v1/auth/login', [
            'email' => 'agent2@test.local',
            'password' => 'mauvais',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_login_ne_distingue_pas_email_inconnu_et_mot_de_passe_faux(): void
    {
        $org = $this->makeOrganisation('T03');
        $this->makeUser($org, 'agent3@test.local');

        $inconnu = $this->postJson('/api/v1/auth/login', [
            'email' => 'personne@test.local',
            'password' => 'secret-123',
        ]);

        $mauvais = $this->postJson('/api/v1/auth/login', [
            'email' => 'agent3@test.local',
            'password' => 'mauvais',
        ]);

        // Des messages différents permettraient d'énumérer les comptes existants.
        $this->assertSame(
            $inconnu->json('errors.email'),
            $mauvais->json('errors.email'),
            "Les deux échecs d'authentification doivent être indiscernables."
        );
    }

    public function test_login_valide_ses_entrees(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'pas-un-email',
            'password' => 'x',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_me_exige_un_token(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_me_retourne_le_profil_et_les_permissions(): void
    {
        $org = $this->makeOrganisation('T04');
        $user = $this->makeUser($org, 'agent4@test.local');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.current_organisation_id', $org->id)
            ->assertJsonStructure(['data' => ['user', 'permissions']]);
    }

    public function test_logout_revoque_le_token_courant(): void
    {
        $org = $this->makeOrganisation('T05');
        $user = $this->makeUser($org, 'agent5@test.local');

        $token = $user->createToken('phpunit')->plainTextToken;

        $this->assertSame(1, $user->tokens()->count());

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();

        // Le token est bien détruit côté serveur.
        $this->assertSame(0, $user->fresh()->tokens()->count());

        // Le guard résolu lors de la requête précédente reste en cache dans l'instance
        // d'application partagée par le test : sans cet oubli explicite, la requête
        // suivante réutiliserait l'utilisateur déjà authentifié et répondrait 200.
        $this->app['auth']->forgetGuards();

        // Le même token ne doit plus ouvrir aucune porte.
        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    /**
     * Rattache un agent à une organisation.
     *
     * `user_organisation_role` impose `role_id` ET `creator_id` en NOT NULL, alors que
     * la relation User::organisations() est un belongsToMany nu : un `attach($orgId)`
     * seul échoue. Toute API de rattachement devra fournir ces deux colonnes (D09).
     */
    private function attachOrganisation(User $user, Organisation $org): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'test-role'],
            ['description' => 'Rôle de test', 'guard_name' => 'web']
        );

        $user->organisations()->attach($org->id, [
            'role_id' => $role->id,
            'creator_id' => $user->id,
        ]);
    }

    public function test_switch_organisation_accepte_une_organisation_rattachee(): void
    {
        $orgA = $this->makeOrganisation('T06');
        $orgB = $this->makeOrganisation('T07');
        $user = $this->makeUser($orgA, 'agent6@test.local');
        $this->attachOrganisation($user, $orgB);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/switch-organisation', ['organisation_id' => $orgB->id])
            ->assertOk()
            ->assertJsonPath('data.user.current_organisation_id', $orgB->id);

        $this->assertSame($orgB->id, $user->fresh()->current_organisation_id);
    }

    public function test_switch_organisation_refuse_une_organisation_non_rattachee(): void
    {
        $orgA = $this->makeOrganisation('T08');
        $orgEtrangere = $this->makeOrganisation('T09');
        $user = $this->makeUser($orgA, 'agent7@test.local');

        // Cœur du risque R03 : sans ce contrôle, n'importe qui se place dans le
        // contexte d'une autre organisation et lit ses données.
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/switch-organisation', ['organisation_id' => $orgEtrangere->id])
            ->assertStatus(403);

        $this->assertSame($orgA->id, $user->fresh()->current_organisation_id);
    }

    public function test_switch_organisation_valide_ses_entrees(): void
    {
        $org = $this->makeOrganisation('T10');
        $user = $this->makeUser($org, 'agent8@test.local');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/switch-organisation', ['organisation_id' => 999999])
            ->assertStatus(422)
            ->assertJsonValidationErrors('organisation_id');
    }
}
