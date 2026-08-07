<?php

namespace Tests\Feature\Api\V1;

use App\Models\Author;
use App\Models\AuthorType;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — auteurs (référentiel). Portage finalisé le 2026-08-04.
 */
class AuthorApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['author'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeType(string $name = null): AuthorType
    {
        // `author_types.name` est unique et `author_types.description` NOT NULL
        // sans valeur par défaut.
        return AuthorType::create([
            'name' => $name ?? 'Type ' . uniqid(),
            'description' => 'Type de test',
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/authors')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/authors')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        Author::create(['name' => 'Durand', 'type_id' => $this->makeType()->id]);
        Author::create(['name' => 'Martin', 'type_id' => $this->makeType()->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/authors')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $author = Author::create(['name' => 'Durand', 'type_id' => $this->makeType()->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/authors/{$author->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Durand');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/authors', ['name' => 'Dupont', 'type_id' => $this->makeType()->id])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Dupont');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/authors', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type_id']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $author = Author::create(['name' => 'Durand', 'type_id' => $this->makeType()->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/authors/{$author->id}", ['lifespan' => '1900-1950'])
            ->assertOk()
            ->assertJsonPath('data.lifespan', '1900-1950');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $author = Author::create(['name' => 'Durand', 'type_id' => $this->makeType()->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/authors/{$author->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    public function test_author_types_retourne_les_types(): void
    {
        $this->makeType('Personne');
        $this->makeType('Collectivité');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/author-types')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $author = Author::create(['name' => 'Partagé', 'type_id' => $this->makeType()->id]);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/authors/{$author->id}")
            ->assertOk();
    }
}
