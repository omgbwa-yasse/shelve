<?php

namespace Tests\Feature\Api\V1;

use App\Models\DeclassementList;
use App\Models\DeclassementStatus;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D07 — listes de déclassement (org-scopées via `organisation_id`, R03).
 * Portage finalisé le 2026-08-04.
 */
class DeclassementListApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['declassement_list'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeList(Organisation $org = null, array $extra = []): DeclassementList
    {
        $org = $org ?? $this->organisation;
        $status = DeclassementStatus::firstOrCreate(['name' => 'Brouillon']);

        return DeclassementList::create([
            'code' => 'DEC-' . substr(uniqid(), -6),
            'name' => 'Liste de déclassement',
            'organisation_id' => $org->id,
            'declassement_status_id' => $status->id,
            'creator_id' => $this->user->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/declassement-lists')->assertStatus(401);
    }

    public function test_index_retourne_uniquement_les_listes_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeList();
        $this->makeList($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/declassement-lists')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_cree_la_liste_avec_le_creator_authentifie(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/declassement-lists', [
                'code' => 'DEC-A',
                'name' => 'Liste A',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'DEC-A')
            ->assertJsonPath('data.creator_id', $this->user->id)
            ->assertJsonPath('data.organisation_id', $this->organisation->id)
            ->assertJsonPath('data.is_approval_requested', false);

        $this->assertDatabaseHas('declassement_lists', ['id' => $response->json('data.id')]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/declassement-lists', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name']);
    }

    public function test_update_modifie_le_nom(): void
    {
        $list = $this->makeList();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/declassement-lists/{$list->id}", ['name' => 'Liste renommée'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Liste renommée');
    }

    public function test_destroy_supprime_la_liste(): void
    {
        $list = $this->makeList();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/declassement-lists/{$list->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('declassement_lists', ['id' => $list->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : une liste d'une autre organisation répond 404 sur
     * show, update et destroy (jamais 403).
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $list = $this->makeList($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/declassement-lists/{$list->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/declassement-lists/{$list->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/declassement-lists/{$list->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('declassement_lists', ['id' => $list->id]);
    }
}
