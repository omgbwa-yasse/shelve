<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalFolderType;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceCategory;
use App\Models\WorkplaceFolder;
use App\Models\WorkplaceMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D12 — dossiers partagés dans un espace de travail (contenu, org-scopé via le
 * workplace parent). Portage finalisé le 2026-08-04.
 */
class WorkplaceFolderApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['workplace_folder'];

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

    private function makeRecordFolder(): RecordDigitalFolder
    {
        $type = RecordDigitalFolderType::create([
            'code' => 'TYPE-' . substr(uniqid(), -6),
            'name' => 'Type dossier',
        ]);

        return RecordDigitalFolder::create([
            'code' => 'FOL-' . substr(uniqid(), -6),
            'name' => 'Dossier',
            'status' => 'active',
            'type_id' => $type->id,
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
        ]);
    }

    private function makeShared(Workplace $workplace, RecordDigitalFolder $folder): WorkplaceFolder
    {
        return WorkplaceFolder::create([
            'workplace_id' => $workplace->id,
            'folder_id' => $folder->id,
            'shared_by' => $this->user->id,
            'shared_at' => now(),
            'access_level' => 'view',
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $workplace = $this->makeWorkplace();

        $this->getJson("/api/v1/workplaces/{$workplace->id}/content/folders")->assertStatus(401);
    }

    public function test_index_retourne_les_dossiers_partages(): void
    {
        $workplace = $this->makeWorkplace();
        $this->makeShared($workplace, $this->makeRecordFolder());

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/content/folders")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_share_partage_un_dossier(): void
    {
        $workplace = $this->makeWorkplace();
        $folder = $this->makeRecordFolder();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/content/folders", [
                'folder_id' => $folder->id,
                'access_level' => 'full',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.access_level', 'full')
            ->assertJsonPath('data.shared_by', $this->user->id);
    }

    public function test_share_valide_ses_entrees(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/content/folders", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['folder_id', 'access_level']);
    }

    public function test_unshare_retire_le_dossier(): void
    {
        $workplace = $this->makeWorkplace();
        $shared = $this->makeShared($workplace, $this->makeRecordFolder());

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workplaces/{$workplace->id}/content/folders/{$shared->id}")
            ->assertOk();

        $this->assertDatabaseMissing('workplace_folders', ['id' => $shared->id]);
    }

    public function test_pin_toggle_l_epinglage(): void
    {
        $workplace = $this->makeWorkplace();
        $shared = $this->makeShared($workplace, $this->makeRecordFolder());

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/content/folders/{$shared->id}/pin")
            ->assertOk()
            ->assertJsonPath('data.is_pinned', true);
    }

    /**
     * ⚠️ Cœur du risque R12 : un workplace d'une autre organisation répond 404.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $workplace = $this->makeWorkplace($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/content/folders")
            ->assertStatus(404);
    }
}
