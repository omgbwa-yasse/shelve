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
 * D12 — espaces de travail (org-scopés via `organisation_id`, R12). Portage
 * finalisé le 2026-08-04.
 *
 * NB : la `WorkplacePolicy` existante autorise `viewAny`/`create` à tout agent
 * authentifié (les permissions n'y sont pas exigées) ; elle n'est pas modifiée
 * ici. L'isolation multi-organisation est portée par le contrôleur (404).
 */
class WorkplaceApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['workplace'];

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

    private function makeWorkplace(Organisation $org = null, array $extra = []): Workplace
    {
        $org = $org ?? $this->organisation;

        $workplace = Workplace::create([
            'code' => 'WP-' . date('Y') . '-' . substr(uniqid(), -4),
            'name' => 'Espace de travail',
            'category_id' => $this->category->id,
            'organisation_id' => $org->id,
            'owner_id' => $this->user->id,
            'created_by' => $this->user->id,
            'status' => 'active',
            ...$extra,
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
        $this->getJson('/api/v1/workplaces')->assertStatus(401);
    }

    public function test_index_retourne_uniquement_les_workplaces_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeWorkplace();
        $this->makeWorkplace($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/workplaces')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Espace de travail');
    }

    public function test_store_cree_la_ressource_avec_le_creator_comme_owner(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workplaces', [
                'name' => 'Nouvel espace',
                'description' => 'Description',
                'category_id' => $this->category->id,
                'is_public' => true,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Nouvel espace')
            ->assertJsonPath('data.owner_id', $this->user->id)
            ->assertJsonPath('data.organisation_id', $this->organisation->id);

        $workplaceId = $response->json('data.id');

        $this->assertDatabaseHas('workplace_members', [
            'workplace_id' => $workplaceId,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workplaces', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category_id']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/workplaces/{$workplace->id}", ['name' => 'Espace renommé'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Espace renommé');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workplaces/{$workplace->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('workplaces', ['id' => $workplace->id]);
    }

    public function test_archive_archive_la_ressource(): void
    {
        $workplace = $this->makeWorkplace();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/archive")
            ->assertOk()
            ->assertJsonPath('data.status', 'archived');
    }

    public function test_show_d_un_non_membre_est_refuse(): void
    {
        $userAutre = User::factory()->forOrganisation($this->organisation)->create();
        $workplace = $this->makeWorkplace();

        $this->actingAs($userAutre, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}")
            ->assertStatus(403);
    }

    /**
     * ⚠️ Cœur du risque R12 : un workplace d'une autre organisation doit renvoyer
     * 404 (jamais 403 — un 403 confirmerait son existence), sur show, update,
     * destroy et archive.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $workplace = $this->makeWorkplace($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplaces/{$workplace->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/workplaces/{$workplace->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workplaces/{$workplace->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplaces/{$workplace->id}/archive")
            ->assertStatus(404);

        $this->assertDatabaseHas('workplaces', ['id' => $workplace->id]);
    }
}
