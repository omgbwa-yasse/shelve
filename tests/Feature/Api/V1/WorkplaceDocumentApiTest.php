<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalDocumentType;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceCategory;
use App\Models\WorkplaceDocument;
use App\Models\WorkplaceMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D12 — documents partagés dans un espace de travail (contenu, org-scopé via le
 * workplace parent). Portage finalisé le 2026-08-04.
 */
class WorkplaceDocumentApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['workplace_document'];

    private User $user;
    private Organisation $organisation;
    private WorkplaceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);

        $this->category = WorkplaceCategory::create(['code' => 'CAT', 'name' => 'Catégorie', 'is_active' => true]);
    }

    private function makeWorkplace(Organisation $org = null): Workplace
    {
        $org = $org ?? $this->organisation;

        $workplace = Workplace::create([
            'code' => 'WP-' . date('Y') . '-' . substr(uniqid(), -4),
            'name' => 'Espace',
            'category_id' => $this->category->id,
            'organisation_id' => $org->id,
            'owner_id' => $this->user->id,
            'created_by' => $this->user->id,
            'status' => 'active',
        ]);

        WorkplaceMember::create([
            'workplace_id' => $workplace->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
            'can_create_folders' => true,
            'can_create_documents' => true,
            'can_delete' => true,
            'can_share' => true,
            'can_invite' => true,
            'joined_at' => now(),
        ]);

        return $workplace;
    }

    private function makeRecordDocument(): RecordDigitalDocument
    {
        $type = RecordDigitalDocumentType::create([
            'code' => 'TYPE-' . substr(uniqid(), -6),
            'name' => 'Type document',
        ]);

        return RecordDigitalDocument::create([
            'code' => 'DOC-' . substr(uniqid(), -6),
            'name' => 'Document',
            'status' => 'draft',
            'type_id' => $type->id,
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
        ]);
    }

    private function makeShared(Workplace $workplace, RecordDigitalDocument $document): WorkplaceDocument
    {
        return WorkplaceDocument::create([
            'workplace_id' => $workplace->id,
            'document_id' => $document->id,
            'shared_by' => $this->user->id,
            'shared_at' => now(),
            'access_level' => 'view',
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $workplace = $this->makeWorkplace();

        $this->getJson("/api/v1/workplaces/{$workplace->id}/content/documents")->assertStatus(401);
    }

    public function test_index_retourne_les_documents_partages(): void
    {
        $workplace = $this->makeWorkplace();
        $this->makeShared($workplace, $this->makeRecordDocument());

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/content/documents")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_share_partage_un_document(): void
    {
        $workplace = $this->makeWorkplace();
        $document = $this->makeRecordDocument();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/content/documents", [
                'document_id' => $document->id,
                'access_level' => 'edit',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.access_level', 'edit')
            ->assertJsonPath('data.shared_by', $this->user->id);
    }

    public function test_share_d_un_document_deja_partage_est_refuse(): void
    {
        $workplace = $this->makeWorkplace();
        $document = $this->makeRecordDocument();
        $this->makeShared($workplace, $document);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/content/documents", [
                'document_id' => $document->id,
                'access_level' => 'view',
            ])
            ->assertStatus(422);
    }

    public function test_share_valide_ses_entrees(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/content/documents", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['document_id', 'access_level']);
    }

    public function test_unshare_retire_le_document(): void
    {
        $workplace = $this->makeWorkplace();
        $shared = $this->makeShared($workplace, $this->makeRecordDocument());

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workplaces/{$workplace->id}/content/documents/{$shared->id}")
            ->assertOk();

        $this->assertDatabaseMissing('workplace_documents', ['id' => $shared->id]);
    }

    public function test_feature_toggle_la_mise_en_avant(): void
    {
        $workplace = $this->makeWorkplace();
        $shared = $this->makeShared($workplace, $this->makeRecordDocument());

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/content/documents/{$shared->id}/feature")
            ->assertOk()
            ->assertJsonPath('data.is_featured', true);
    }

    /**
     * ⚠️ Cœur du risque R12 : un workplace d'une autre organisation répond 404.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $workplace = $this->makeWorkplace($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/content/documents")
            ->assertStatus(404);
    }
}
