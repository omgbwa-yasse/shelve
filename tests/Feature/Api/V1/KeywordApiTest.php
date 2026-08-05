<?php

namespace Tests\Feature\Api\V1;

use App\Models\Keyword;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — mots-clés (référentiel). Portage finalisé le 2026-08-04.
 */
class KeywordApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['keyword'];

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
        $this->getJson('/api/v1/keywords')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/keywords')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        Keyword::create(['name' => 'Archives']);
        Keyword::create(['name' => 'Gestion']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/keywords')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $keyword = Keyword::create(['name' => 'Archives']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/keywords/{$keyword->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Archives');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/keywords', ['name' => '  Nouveau  '])
            ->assertStatus(201)
            // Le contrôleur Blade trimme le nom : le comportement est conservé.
            ->assertJsonPath('data.name', 'Nouveau');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/keywords', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $keyword = Keyword::create(['name' => 'Archives']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/keywords/{$keyword->id}", ['name' => 'Gestion'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Gestion');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $keyword = Keyword::create(['name' => 'À supprimer']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/keywords/{$keyword->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('keywords', ['id' => $keyword->id]);
    }

    public function test_search_retourne_les_mots_cles(): void
    {
        Keyword::create(['name' => 'Archives']);
        Keyword::create(['name' => 'Archivage numérique']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/keywords/search?q=arch')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_search_trop_court_retourne_vide(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/keywords/search?q=a')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_process_cree_et_retourne_les_ids(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/keywords/process', ['keywords' => 'A ; B ; A'])
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

        $keyword = Keyword::create(['name' => 'Partagé']);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/keywords/{$keyword->id}")
            ->assertOk();
    }
}
