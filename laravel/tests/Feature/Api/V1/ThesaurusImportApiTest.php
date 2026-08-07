<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\ThesaurusConcept;
use App\Models\ThesaurusImport;
use App\Models\ThesaurusLabel;
use App\Models\ThesaurusScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D08 — import thésaurus (SKOS-RDF/CSV/JSON). Porté le 2026-08-05.
 */
class ThesaurusImportApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['thesaurus_concept', 'thesaurus_scheme'];

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
            'title' => 'Schéma cible',
            'language' => 'fr-fr',
            'uri' => config('app.url') . '/thesaurus/schemes/cible-' . substr(uniqid(), -4),
        ]);
    }

    private function skosFixture(): UploadedFile
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:skos="http://www.w3.org/2004/02/skos/core#"
         xmlns:dc="http://purl.org/dc/elements/1.1/">
  <skos:Concept rdf:about="https://exemple.fr/concepts/test-archives">
    <skos:prefLabel xml:lang="fr">Archives</skos:prefLabel>
    <skos:notation>ARCH</skos:notation>
  </skos:Concept>
</rdf:RDF>
XML;

        return UploadedFile::fake()->createWithContent('thesaurus.rdf', $xml);
    }

    public function test_import_exige_une_authentification(): void
    {
        $this->postJson('/api/v1/thesaurus/import')->assertStatus(401);
    }

    public function test_import_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/thesaurus/import', [
                'file' => $this->skosFixture(),
                'format' => 'skos-rdf',
                'merge_mode' => 'append',
            ])
            ->assertStatus(403);
    }

    public function test_import_importe_un_fichier_skos(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/thesaurus/import', [
                'file' => $this->skosFixture(),
                'format' => 'skos-rdf',
                'scheme_id' => $this->scheme->id,
                'language' => 'fr-fr',
                'merge_mode' => 'append',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.created', 1);

        $importId = $response->json('data.import_id');
        $response->assertHeader('Location', "/api/v1/thesaurus/imports/{$importId}");

        $this->assertDatabaseHas('thesaurus_imports', ['id' => $importId, 'status' => 'completed']);

        $concept = ThesaurusConcept::where('scheme_id', $this->scheme->id)
            ->where('uri', 'https://exemple.fr/concepts/test-archives')
            ->firstOrFail();

        $this->assertDatabaseHas('thesaurus_labels', [
            'concept_id' => $concept->id,
            'type' => 'prefLabel',
            'literal_form' => 'Archives',
        ]);
    }

    public function test_statut_retourne_une_import(): void
    {
        $import = ThesaurusImport::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'csv',
            'filename' => 'fichier.csv',
            'status' => 'completed',
            'created_items' => 4,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/thesaurus/imports/{$import->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.created_items', 4);
    }

    public function test_import_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/thesaurus/import', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file', 'format', 'merge_mode']);
    }
}
