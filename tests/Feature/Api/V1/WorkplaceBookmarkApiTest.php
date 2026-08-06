<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceBookmark;
use App\Models\WorkplaceCategory;
use App\Models\WorkplaceMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D12 — favoris d'un espace de travail (ressource imbriquée, org-scopée via le
 * workplace parent). Portage finalisé le 2026-08-04.
 */
class WorkplaceBookmarkApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['workplace_bookmark'];

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
            'joined_at' => now(),
        ]);

        return $workplace;
    }

    private function makeBookmark(Workplace $workplace, int $userId = null): WorkplaceBookmark
    {
        return WorkplaceBookmark::create([
            'workplace_id' => $workplace->id,
            'user_id' => $userId ?? $this->user->id,
            'bookmarkable_type' => 'App\\Models\\Record',
            'bookmarkable_id' => 1,
            'note' => 'Favori',
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $workplace = $this->makeWorkplace();

        $this->getJson("/api/v1/workplaces/{$workplace->id}/bookmarks")->assertStatus(401);
    }

    public function test_index_non_membre_est_refuse(): void
    {
        $userAutre = User::factory()->forOrganisation($this->organisation)->create();
        $workplace = $this->makeWorkplace();

        $this->actingAs($userAutre, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/bookmarks")
            ->assertStatus(403);
    }

    public function test_index_retourne_les_favoris_de_l_utilisateur(): void
    {
        $workplace = $this->makeWorkplace();
        $this->makeBookmark($workplace);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/bookmarks")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_store_cree_puis_toggle_le_favori(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/bookmarks", [
                'bookmarkable_type' => 'App\\Models\\Record',
                'bookmarkable_id' => 42,
                'note' => 'Favori',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.user_id', $this->user->id);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/bookmarks", [
                'bookmarkable_type' => 'App\\Models\\Record',
                'bookmarkable_id' => 42,
            ])
            ->assertOk()
            ->assertJsonPath('deleted', true);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/bookmarks", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['bookmarkable_type', 'bookmarkable_id']);
    }

    public function test_destroy_supprime_le_favori(): void
    {
        $workplace = $this->makeWorkplace();
        $bookmark = $this->makeBookmark($workplace);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workplaces/{$workplace->id}/bookmarks/{$bookmark->id}")
            ->assertOk()
            ->assertJsonPath('deleted', true);

        $this->assertDatabaseMissing('workplace_bookmarks', ['id' => $bookmark->id]);
    }

    public function test_destroy_d_un_favori_etranger_est_refuse(): void
    {
        $workplace = $this->makeWorkplace();
        $autre = User::factory()->forOrganisation($this->organisation)->create();
        $bookmark = $this->makeBookmark($workplace, $autre->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workplaces/{$workplace->id}/bookmarks/{$bookmark->id}")
            ->assertStatus(403);
    }

    /**
     * ⚠️ Cœur du risque R12 : un workplace d'une autre organisation répond 404
     * (jamais 403 — un 403 confirmerait son existence).
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $workplace = $this->makeWorkplace($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/bookmarks")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/bookmarks", [
                'bookmarkable_type' => 'App\\Models\\Record',
                'bookmarkable_id' => 1,
            ])
            ->assertStatus(404);
    }
}
