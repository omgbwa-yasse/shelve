<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceActivity;
use App\Models\WorkplaceCategory;
use App\Models\WorkplaceMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D12 — activité d'un espace de travail (ressource imbriquée, lecture seule,
 * org-scopée via le workplace parent). Portage finalisé le 2026-08-04.
 */
class WorkplaceActivityApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['workplace_activity'];

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

    private function makeActivity(Workplace $workplace): WorkplaceActivity
    {
        return WorkplaceActivity::create([
            'workplace_id' => $workplace->id,
            'user_id' => $this->user->id,
            'activity_type' => 'shared_document',
            'description' => 'Document partagé',
            'created_at' => now(),
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $workplace = $this->makeWorkplace();

        $this->getJson("/api/v1/workplaces/{$workplace->id}/activities")->assertStatus(401);
    }

    public function test_index_non_membre_est_refuse(): void
    {
        $userAutre = User::factory()->forOrganisation($this->organisation)->create();
        $workplace = $this->makeWorkplace();

        $this->actingAs($userAutre, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/activities")
            ->assertStatus(403);
    }

    public function test_index_retourne_les_activites(): void
    {
        $workplace = $this->makeWorkplace();
        $this->makeActivity($workplace);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/activities")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.activity_type', 'shared_document');
    }

    public function test_index_peut_filtrer_par_type(): void
    {
        $workplace = $this->makeWorkplace();
        $this->makeActivity($workplace);
        WorkplaceActivity::create([
            'workplace_id' => $workplace->id,
            'user_id' => $this->user->id,
            'activity_type' => 'deleted_document',
            'created_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/activities?filter[activity_type]=deleted_document")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /**
     * ⚠️ Cœur du risque R12 : un workplace d'une autre organisation répond 404.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $workplace = $this->makeWorkplace($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}/activities")
            ->assertStatus(404);
    }
}
