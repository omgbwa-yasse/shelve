<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\BackupPlanning;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D16 — planifications de sauvegarde. Portage finalisé le 2026-08-04.
 */
class BackupPlanningApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['backup_planning'];

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
        $this->getJson('/api/v1/backup-plannings')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/backup-plannings')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/backup-plannings')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/backup-plannings', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['backup_id', 'frequence', 'hour']);
    }
}
