<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceCategory;
use App\Models\WorkplaceMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D12 — membres d'un espace de travail (ressource imbriquée, org-scopée via le
 * workplace parent). Portage finalisé le 2026-08-04.
 */
class WorkplaceMemberApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['workplace_member'];

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

    public function test_index_exige_une_authentification(): void
    {
        $workplace = $this->makeWorkplace();

        $this->getJson("/api/v1/workplaces/{$workplace->id}/members")->assertStatus(401);
    }

    public function test_index_non_membre_est_refuse(): void
    {
        $userAutre = User::factory()->forOrganisation($this->organisation)->create();
        $workplace = $this->makeWorkplace();

        $this->actingAs($userAutre, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/members")
            ->assertStatus(403);
    }

    public function test_index_retourne_les_membres(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/members")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.role', 'owner');
    }

    public function test_store_ajoute_un_membre_existant(): void
    {
        $workplace = $this->makeWorkplace();
        $nouveau = User::factory()->forOrganisation($this->organisation)->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/members", [
                'user_id' => $nouveau->id,
                'role' => 'editor',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.user_id', $nouveau->id)
            ->assertJsonPath('data.can_share', true);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/members", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('role');
    }

    public function test_update_change_le_role(): void
    {
        $workplace = $this->makeWorkplace();
        $nouveau = User::factory()->forOrganisation($this->organisation)->create();

        $member = WorkplaceMember::create([
            'workplace_id' => $workplace->id,
            'user_id' => $nouveau->id,
            'role' => 'viewer',
            'joined_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/workplaces/{$workplace->id}/members/{$member->id}", ['role' => 'admin'])
            ->assertOk()
            ->assertJsonPath('data.role', 'admin');
    }

    public function test_update_permissions_et_notifications(): void
    {
        $workplace = $this->makeWorkplace();

        $member = $workplace->members()->first();

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/workplaces/{$workplace->id}/members/{$member->id}/permissions", ['can_share' => false])
            ->assertOk()
            ->assertJsonPath('data.can_share', false);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/workplaces/{$workplace->id}/members/{$member->id}/notifications", ['notify_on_updates' => true])
            ->assertOk()
            ->assertJsonPath('data.notify_on_updates', true);
    }

    public function test_destroy_ne_permet_pas_de_retirer_le_proprietaire(): void
    {
        $workplace = $this->makeWorkplace();
        $owner = $workplace->members()->where('role', 'owner')->first();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workplaces/{$workplace->id}/members/{$owner->id}")
            ->assertStatus(403);
    }

    /**
     * ⚠️ Cœur du risque R12 : un workplace d'une autre organisation répond 404.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $workplace = $this->makeWorkplace($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/members")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/members", ['role' => 'viewer'])
            ->assertStatus(404);
    }
}
