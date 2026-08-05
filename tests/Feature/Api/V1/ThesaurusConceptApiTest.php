<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\ThesaurusConcept;
use App\Models\ThesaurusLabel;
use App\Models\ThesaurusScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D08 — concepts de thésaurus (référentiel global, SKOS). Portage finalisé le 2026-08-04.
 */
class ThesaurusConceptApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['thesaurus_concept'];

    private User $user;
    private ThesaurusScheme $scheme;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);

        $this->scheme = ThesaurusScheme::create([
            'identifier' => 'SC' . substr(uniqid(), -6),
            'title' => 'Schéma',
            'language' => 'fr-fr',
            'uri' => config('app.url') . '/thesaurus/schemes/schema-test',
        ]);
    }

    private function makeConcept(array $extra = []): ThesaurusConcept
    {
        return ThesaurusConcept::create([
            'scheme_id' => $this->scheme->id,
            'uri' => config('app.url') . '/thesaurus/concepts/concept-' . substr(uniqid(), -6),
            'notation' => 'N' . substr(uniqid(), -4),
            'status' => 1,
            ...$extra,
        ]);
    }

    private function makeLabel(ThesaurusConcept $concept, string $literal, string $type = 'prefLabel'): ThesaurusLabel
    {
        return ThesaurusLabel::create([
            'concept_id' => $concept->id,
            'uri' => $concept->uri . '#label-' . substr(uniqid(), -4),
            'type' => $type,
            'literal_form' => $literal,
            'language' => 'fr-fr',
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/thesaurus-concepts')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/thesaurus-concepts')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makeConcept();
        $this->makeConcept();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/thesaurus-concepts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_peut_filtrer_par_schema(): void
    {
        $this->makeConcept();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/thesaurus-concepts?filter[scheme_id]=' . $this->scheme->id)
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_show_retourne_la_ressource(): void
    {
        $concept = $this->makeConcept();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/thesaurus-concepts/{$concept->id}")
            ->assertOk()
            ->assertJsonPath('data.notation', $concept->notation);
    }

    public function test_store_cree_la_ressource(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/thesaurus-concepts', [
                'scheme_id' => $this->scheme->id,
                'uri' => 'https://exemple.fr/concepts/alpha',
                'notation' => 'ALPHA',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.notation', 'ALPHA');

        $response->assertHeader('Location', "/api/v1/thesaurus-concepts/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/thesaurus-concepts', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('scheme_id');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $concept = $this->makeConcept();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/thesaurus-concepts/{$concept->id}", ['notation' => 'BETA'])
            ->assertOk()
            ->assertJsonPath('data.notation', 'BETA');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $concept = $this->makeConcept();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/thesaurus-concepts/{$concept->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('thesaurus_concepts', ['id' => $concept->id]);
    }

    public function test_search_retourne_les_concepts_par_critere(): void
    {
        $concept = $this->makeConcept();
        $this->makeLabel($concept, 'Gestion des archives');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/thesaurus-concepts/search?query=archives')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $concept->id);
    }

    public function test_autocomplete_retourne_les_concepts_correspondants(): void
    {
        $concept = $this->makeConcept();
        $this->makeLabel($concept, 'Gestion des archives');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/thesaurus-concepts/autocomplete?q=ar')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $concept->id);
    }
}
