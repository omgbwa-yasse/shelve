<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceCategory;
use App\Models\WorkplaceMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D12 — bibliothèque Documents d'un espace de travail (dossiers + fichiers
 * basés sur `records`), ajoutée le 2026-08-06.
 */
class WorkplaceDocumentApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private User $user;
    private Organisation $organisation;
    private WorkplaceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, ['workplace', 'records']);

        $this->category = WorkplaceCategory::create(['code' => 'CAT', 'name' => 'Catégorie', 'is_active' => true]);

        // Les notices exigent level_id/status_id (NOT NULL) : s'assurer qu'un
        // niveau et un statut existent pour la création des documents.
        if (! RecordLevel::exists()) {
            RecordLevel::create(['name' => 'Dossier', 'description' => 'Niveau par défaut']);
        }
        if (! RecordStatus::exists()) {
            RecordStatus::create(['name' => 'Brouillon', 'description' => 'Statut par défaut']);
        }
    }

    private function makeWorkplace(string $code = 'rh'): Workplace
    {
        $workplace = Workplace::create([
            'code' => $code,
            'name' => 'Espace RH',
            'category_id' => $this->category->id,
            'organisation_id' => $this->organisation->id,
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

    public function test_index_retourne_les_elements_de_la_racine(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/workplaces/rh/documents')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_store_folder_puis_upload_dans_le_dossier(): void
    {
        Storage::fake('local');

        $this->makeWorkplace();

        $folder = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workplaces/rh/folders', ['name' => 'Arrivées 2026'])
            ->assertStatus(201)
            ->assertJsonPath('data.is_folder', true)
            ->json('data');

        $this->actingAs($this->user, 'sanctum')
            ->post('/api/v1/workplaces/rh/documents/upload', [
                'file' => UploadedFile::fake()->create('rapport.pdf', 100, 'application/pdf'),
                'name' => 'Rapport annuel.pdf',
                'parent_id' => $folder['id'],
            ], ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJsonPath('data.is_folder', false)
            ->assertJsonPath('data.parent_id', $folder['id'])
            ->assertJsonPath('data.attachment.name', 'Rapport annuel.pdf');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/workplaces/rh/documents')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/rh/documents?parent_id={$folder['id']}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Rapport annuel.pdf');
    }

    public function test_download_renvoie_le_fichier(): void
    {
        Storage::fake('local');

        $this->makeWorkplace();

        $document = $this->actingAs($this->user, 'sanctum')
            ->post('/api/v1/workplaces/rh/documents/upload', [
                'file' => UploadedFile::fake()->create('archive.zip', 100),
                'name' => 'archive.zip',
            ], ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->json('data');

        $this->actingAs($this->user, 'sanctum')
            ->get("/api/v1/workplaces/rh/documents/{$document['id']}/download")
            ->assertOk();
    }

    public function test_destroy_supprime_le_dossier_et_ses_descendants(): void
    {
        Storage::fake('local');

        $this->makeWorkplace();

        $folder = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workplaces/rh/folders', ['name' => 'Dossier'])
            ->assertStatus(201)
            ->json('data');

        $doc = $this->actingAs($this->user, 'sanctum')
            ->post('/api/v1/workplaces/rh/documents/upload', [
                'file' => UploadedFile::fake()->create('fichier.txt', 10),
                'parent_id' => $folder['id'],
            ], ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->json('data');

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workplaces/rh/documents/{$folder['id']}")
            ->assertNoContent();

        $this->assertDatabaseMissing('records', ['id' => $folder['id']]);
        $this->assertDatabaseMissing('records', ['id' => $doc['id']]);
    }

    public function test_un_non_membre_ne_peut_pas_lister_les_documents(): void
    {
        $workplace = $this->makeWorkplace();
        $userAutre = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($userAutre, ['workplace']);

        $this->actingAs($userAutre, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->code}/documents")
            ->assertStatus(403);
    }

    private function makeDocument(Workplace $workplace, string $name = 'rapport.pdf'): array
    {
        Storage::fake('local');

        return $this->actingAs($this->user, 'sanctum')
            ->post("/api/v1/workplaces/{$workplace->code}/documents/upload", [
                'file' => UploadedFile::fake()->create($name, 100),
            ], ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->json('data');
    }

    public function test_share_rend_le_document_visible_du_module_records(): void
    {
        $workplace = $this->makeWorkplace();
        $doc = $this->makeDocument($workplace);

        // Avant partage : invisible du module Records.
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/records')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/rh/documents/{$doc['id']}/share")
            ->assertOk()
            ->assertJsonPath('data.is_shared', true);

        // Après partage : visible du module Records, toujours dans le workplace.
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/records')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/workplaces/rh/documents')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        // Le show du module Records est accessible.
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$doc['id']}")
            ->assertOk();
    }

    public function test_unshare_rend_le_document_invisible_du_module_records(): void
    {
        $workplace = $this->makeWorkplace();
        $doc = $this->makeDocument($workplace);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/rh/documents/{$doc['id']}/share")
            ->assertOk();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/rh/documents/{$doc['id']}/unshare")
            ->assertOk()
            ->assertJsonPath('data.is_shared', false);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/records')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    public function test_transfer_vers_records_le_sort_du_workplace_et_l_affecte_au_plan(): void
    {
        $workplace = $this->makeWorkplace();
        $doc = $this->makeDocument($workplace);

        $activity = \App\Models\Activity::create(['code' => 'ACT', 'name' => 'Ressources humaines']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/rh/documents/{$doc['id']}/transfer", ['activity_id' => $activity->id])
            ->assertOk()
            ->assertJsonPath('data.transferred', true);

        // Plus dans le workplace.
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/workplaces/rh/documents')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // Visible du module Records, affectée au plan de classement.
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$doc['id']}")
            ->assertOk()
            ->assertJsonPath('data.workplace_id', null)
            ->assertJsonPath('data.activity_id', $activity->id);
    }

    public function test_transfer_exige_une_activite(): void
    {
        $workplace = $this->makeWorkplace();
        $doc = $this->makeDocument($workplace);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/rh/documents/{$doc['id']}/transfer", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['activity_id']);
    }
}
