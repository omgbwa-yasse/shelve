<?php

namespace Tests\Feature\Api\V1;

use App\Models\Author;
use App\Models\AuthorContact;
use App\Models\AuthorType;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — contacts d'auteurs (référentiel). Portage finalisé le 2026-08-04.
 */
class AuthorContactApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['author_contact'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeAuthor(): Author
    {
        // `author_types.name` est unique, `author_types.description` NOT NULL sans défaut.
        return Author::create(['name' => 'Durand', 'type_id' => AuthorType::create(['name' => 'Type ' . uniqid(), 'description' => 'Type de test'])->id]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/author-contacts')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/author-contacts')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $author = $this->makeAuthor();
        AuthorContact::create(['author_id' => $author->id, 'email' => 'a@test.local']);
        AuthorContact::create(['author_id' => $author->id, 'email' => 'b@test.local']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/author-contacts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $contact = AuthorContact::create(['author_id' => $this->makeAuthor()->id, 'email' => 'a@test.local']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/author-contacts/{$contact->id}")
            ->assertOk()
            ->assertJsonPath('data.email', 'a@test.local');
    }

    public function test_store_cree_la_ressource(): void
    {
        $author = $this->makeAuthor();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/author-contacts', ['author_id' => $author->id, 'email' => 'c@test.local'])
            ->assertStatus(201)
            ->assertJsonPath('data.email', 'c@test.local');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/author-contacts', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('author_id');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $contact = AuthorContact::create(['author_id' => $this->makeAuthor()->id, 'email' => 'a@test.local']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/author-contacts/{$contact->id}", ['phone1' => '01 23 45'])
            ->assertOk()
            ->assertJsonPath('data.phone1', '01 23 45');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $contact = AuthorContact::create(['author_id' => $this->makeAuthor()->id, 'email' => 'a@test.local']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/author-contacts/{$contact->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('author_contacts', ['id' => $contact->id]);
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $contact = AuthorContact::create(['author_id' => $this->makeAuthor()->id, 'email' => 'a@test.local']);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/author-contacts/{$contact->id}")
            ->assertOk();
    }
}
