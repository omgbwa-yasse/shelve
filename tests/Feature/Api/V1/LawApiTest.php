<?php

namespace Tests\Feature\Api\V1;

use App\Models\Law;
use App\Models\LawType;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — lois (référentiel). Portage finalisé le 2026-08-04.
 */
class LawApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['law'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeLawType(): LawType
    {
        return LawType::create(['name' => 'Loi']);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/laws')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/laws')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $type = $this->makeLawType();
        Law::create(['code' => 'L1', 'name' => 'Loi 1', 'publish_date' => '2020-01-01', 'law_type_id' => $type->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/laws')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $law = Law::create(['code' => 'L1', 'name' => 'Loi 1', 'publish_date' => '2020-01-01', 'law_type_id' => $this->makeLawType()->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/laws/{$law->id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'L1');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/laws', [
                'code' => 'L2',
                'name' => 'Loi 2',
                'publish_date' => '2021-05-05',
                'law_type_id' => $this->makeLawType()->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'L2');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/laws', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'publish_date', 'law_type_id']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $law = Law::create(['code' => 'L1', 'name' => 'Loi 1', 'publish_date' => '2020-01-01', 'law_type_id' => $this->makeLawType()->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/laws/{$law->id}", ['name' => 'Loi 1 bis'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Loi 1 bis');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $law = Law::create(['code' => 'L1', 'name' => 'Loi 1', 'publish_date' => '2020-01-01', 'law_type_id' => $this->makeLawType()->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/laws/{$law->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('laws', ['id' => $law->id]);
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $law = Law::create(['code' => 'L1', 'name' => 'Loi 1', 'publish_date' => '2020-01-01', 'law_type_id' => $this->makeLawType()->id]);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/laws/{$law->id}")
            ->assertOk();
    }
}
