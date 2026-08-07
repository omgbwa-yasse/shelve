<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\ThesaurusScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D08 — schémas de thésaurus (référentiel global, SKOS). Portage finalisé le 2026-08-04.
 */
class ThesaurusSchemeApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['thesaurus_scheme'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeScheme(array $extra = []): ThesaurusScheme
    {
        return ThesaurusScheme::create([
            'identifier' => 'ID' . substr(uniqid(), -6),
            'title' => 'Schéma de test',
            'description' => 'Description',
            'language' => 'fr-fr',
            'uri' => config('app.url') . '/thesaurus/schemes/schema-' . substr(uniqid(), -6),
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/thesaurus-schemes')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/thesaurus-schemes')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makeScheme();
        $this->makeScheme();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/thesaurus-schemes')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $scheme = $this->makeScheme();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/thesaurus-schemes/{$scheme->id}")
            ->assertOk()
            ->assertJsonPath('data.identifier', $scheme->identifier)
            ->assertJsonPath('data.formatted_title', $scheme->formatted_title);
    }

    public function test_store_cree_la_ressource_avec_uri_generee(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/thesaurus-schemes', [
                'identifier' => 'mon-schema',
                'title' => 'Mon schéma',
                'description' => 'Description',
                'language' => 'fr-fr',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.identifier', 'mon-schema')
            ->assertJsonPath('data.uri', config('app.url') . '/thesaurus/schemes/mon-schema');

        $response->assertHeader('Location', "/api/v1/thesaurus-schemes/{$response->json('data.id')}");
    }

    public function test_store_creer_un_namespace_optionnel(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/thesaurus-schemes', [
                'identifier' => 'ns-test',
                'title' => 'Schéma avec namespace',
                'language' => 'fr-fr',
                'namespace_uri' => 'https://exemple.fr/ns/',
            ])
            ->assertStatus(201)
            ->assertJsonStructure(['data' => ['namespace_id']]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/thesaurus-schemes', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['identifier', 'title', 'language']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $scheme = $this->makeScheme();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/thesaurus-schemes/{$scheme->id}", ['title' => 'Schéma renommé'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Schéma renommé');
    }

    public function test_update_verifie_l_unicite_hors_ressource_courante(): void
    {
        $a = $this->makeScheme(['identifier' => 'ID-A']);
        $b = $this->makeScheme(['identifier' => 'ID-B']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/thesaurus-schemes/{$b->id}", ['identifier' => 'ID-A'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('identifier');

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/thesaurus-schemes/{$a->id}", ['identifier' => 'ID-A'])
            ->assertOk();
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $scheme = $this->makeScheme();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/thesaurus-schemes/{$scheme->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('thesaurus_schemes', ['id' => $scheme->id]);
    }
}
