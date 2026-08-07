<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D09 — agents (`users`), référentiel global. Portage finalisé le 2026-08-05.
 *
 * Préfixe des permissions : `users_*` (voir UserPolicy) — le trait génère
 * `users_view`, `users_create`, `users_update`, `users_delete`, couverts.
 */
class UserApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['users'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/users')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        User::factory()->forOrganisation(Organisation::factory()->create())->create();
        User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']])
            ->assertJsonPath('meta.total', User::count());
    }

    public function test_show_retourne_l_agent_sans_secrets(): void
    {
        $target = User::factory()->forOrganisation(Organisation::factory()->create())
            ->create(['email' => 'cible@example.test']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/users/{$target->id}")
            ->assertOk()
            ->assertJsonPath('data.email', 'cible@example.test')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');
    }

    public function test_store_cree_un_agent_avec_le_mot_de_passe_hache(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'Jean',
                'surname' => 'Dupont',
                'birthday' => '1990-01-01',
                'email' => 'jean.dupont@example.test',
                'password' => 'secret123',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.email', 'jean.dupont@example.test')
            ->assertJsonPath('data.birthday', '1990-01-01')
            ->assertJsonMissingPath('data.password');

        $response->assertHeader('Location', "/api/v1/users/{$response->json('data.id')}");

        $user = User::find($response->json('data.id'));
        $this->assertNotNull($user);
        $this->assertNotSame('secret123', $user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_store_valide_ses_entrees(): void
    {
        // `birthday` est NOT NULL sans défaut : son absence doit être refusée.
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/users', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'birthday', 'email', 'password']);

        User::factory()->forOrganisation(Organisation::factory()->create())
            ->create(['email' => 'pris@example.test']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'Jean',
                'birthday' => '1990-01-01',
                'email' => 'pris@example.test',
                'password' => 'secret123',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_update_modifie_l_agent(): void
    {
        $target = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/users/{$target->id}", ['surname' => 'Renommé'])
            ->assertOk()
            ->assertJsonPath('data.surname', 'Renommé');
    }

    public function test_update_rehache_un_nouveau_mot_de_passe(): void
    {
        $target = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/users/{$target->id}", ['password' => 'nouveauMot2Passe'])
            ->assertOk()
            ->assertJsonMissingPath('data.password');

        $this->assertTrue(Hash::check('nouveauMot2Passe', User::find($target->id)->password));
    }

    public function test_destroy_supprime_l_agent(): void
    {
        $target = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/users/{$target->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    /**
     * Référentiel global : un agent d'une autre organisation lit les mêmes agents.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $target = User::factory()->forOrganisation($this->user->organisation)->create();

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/users/{$target->id}")
            ->assertOk();
    }
}
