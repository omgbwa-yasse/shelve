<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\RecordDigitalDocumentMetadataProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * @generated par `php artisan make:api-resource-set` — domaine D02.
 *
 * CE FICHIER EST UN POINT DE DÉPART, PAS UN LIVRABLE.
 * Les règles ci-dessous sont déduites du schéma et des règles déjà présentes dans le
 * contrôleur Blade. Le schéma ne connaît ni les règles métier ni ce que la vue imposait
 * implicitement (risques R01 et R02) : relire le contrôleur ET ses vues avant de valider.
 *
 * Retirer ce bandeau une fois le fichier relu.
 */
class RecordDigitalDocumentMetadataProfileApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    /**
     * Préfixe des permissions pour cette ressource (voir RecordDigitalDocumentMetadataProfilePolicy).
     *
     * ⚠️ Déduit de $model par snake_case — À VÉRIFIER : certaines Policies
     * réutilisent le préfixe d'une ressource parente (ex. LawArticlePolicy utilise
     * 'law_*', pas 'law_article_*'). Ouvrir app/Policies/RecordDigitalDocumentMetadataProfilePolicy.php pour
     * confirmer avant de faire confiance à ce test.
     */
    private const PERMISSIONS = ['record_digital_document_metadata_profile'];

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
        $this->getJson('/api/v1/record-digital-document-metadata-profiles')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/record-digital-document-metadata-profiles')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/record-digital-document-metadata-profiles')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/record-digital-document-metadata-profiles', [])
            ->assertStatus(422);
    }

    // TODO show, update, destroy, filtres, tri, isolation multi-organisation (R03),
    // actions métier — compléter selon la ressource (voir D01/D03 pour le gabarit).
}
