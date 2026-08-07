<?php

namespace Tests\Feature\Api\V1;

use App\Models\Dolly;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D11 — chariots (org-scopés via `owner_organisation_id`, R03). Portage finalisé le 2026-08-04.
 */
class DollyApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['dolly'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeDolly(Organisation $org = null, array $extra = []): Dolly
    {
        $org = $org ?? $this->organisation;

        return Dolly::create([
            'name' => 'Chariot ' . substr(uniqid(), -6),
            'description' => 'Description',
            'category' => 'record',
            'is_public' => false,
            'created_by' => $this->user->id,
            'owner_organisation_id' => $org->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/dollies')->assertStatus(401);
    }

    public function test_index_retourne_uniquement_les_chariots_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeDolly();
        $this->makeDolly($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/dollies')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $dolly = $this->makeDolly();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/dollies/{$dolly->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $dolly->name)
            ->assertJsonPath('data.is_public', false);
    }

    public function test_store_cree_le_chariot_avec_le_creator_authentifie(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/dollies', [
                'name' => 'Chariot A',
                'description' => 'Description A',
                'category' => 'record',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Chariot A')
            ->assertJsonPath('data.created_by', $this->user->id)
            ->assertJsonPath('data.owner_organisation_id', $this->organisation->id)
            ->assertJsonPath('data.is_public', false);

        $response->assertHeader('Location', "/api/v1/dollies/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/dollies', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description', 'category']);
    }

    public function test_update_modifie_le_chariot(): void
    {
        $dolly = $this->makeDolly();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/dollies/{$dolly->id}", ['name' => 'Chariot renommé'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Chariot renommé');
    }

    public function test_add_et_remove_un_element(): void
    {
        $dolly = $this->makeDolly();

        $level = \App\Models\RecordLevel::create(['name' => 'Dossier']);
        $status = \App\Models\RecordStatus::create(['name' => 'Actif']);
        $record = \App\Models\Record::create([
            'code' => 'REC-' . substr(uniqid(), -6),
            'name' => 'Notice ' . substr(uniqid(), -6),
            'level_id' => $level->id,
            'status_id' => $status->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/dollies/{$dolly->id}/add-record", ['record_id' => $record->id])
            ->assertOk();

        $this->assertDatabaseHas('dolly_records', [
            'dolly_id' => $dolly->id,
            'record_id' => $record->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/dollies/{$dolly->id}/remove-record/{$record->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('dolly_records', [
            'dolly_id' => $dolly->id,
            'record_id' => $record->id,
        ]);
    }

    public function test_add_et_remove_un_slip(): void
    {
        $dolly = $this->makeDolly();
        $slipStatus = \App\Models\SlipStatus::create(['name' => 'Brouillon']);
        $slip = \App\Models\Slip::create([
            'code' => 'SL' . substr(uniqid(), -6),
            'name' => 'Bordereau ' . substr(uniqid(), -6),
            'officer_organisation_id' => $this->organisation->id,
            'officer_id' => $this->user->id,
            'user_organisation_id' => $this->organisation->id,
            'slip_status_id' => $slipStatus->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/dollies/{$dolly->id}/add-slip", ['slip_id' => $slip->id])
            ->assertOk();

        $this->assertDatabaseHas('dolly_slips', [
            'dolly_id' => $dolly->id,
            'slip_id' => $slip->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/dollies/{$dolly->id}/remove-slip/{$slip->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('dolly_slips', [
            'dolly_id' => $dolly->id,
            'slip_id' => $slip->id,
        ]);
    }

    public function test_rename_modifie_le_nom_du_chariot(): void
    {
        $dolly = $this->makeDolly();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/dollies/{$dolly->id}/rename", ['name' => 'Chariot renommé'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Chariot renommé');
    }

    public function test_clear_vide_le_chariot_sans_supprimer_les_entites(): void
    {
        $dolly = $this->makeDolly();

        $slipStatus = \App\Models\SlipStatus::create(['name' => 'Brouillon']);
        $slip = \App\Models\Slip::create([
            'code' => 'SL' . substr(uniqid(), -6),
            'name' => 'Bordereau ' . substr(uniqid(), -6),
            'officer_organisation_id' => $this->organisation->id,
            'officer_id' => $this->user->id,
            'user_organisation_id' => $this->organisation->id,
            'slip_status_id' => $slipStatus->id,
        ]);
        $dolly->slips()->attach($slip->id);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/dollies/{$dolly->id}/clear")
            ->assertOk();

        $this->assertDatabaseMissing('dolly_slips', ['dolly_id' => $dolly->id, 'slip_id' => $slip->id]);
        $this->assertDatabaseHas('slips', ['id' => $slip->id]);
    }

    public function test_destroy_supprime_le_chariot(): void
    {
        $dolly = $this->makeDolly();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/dollies/{$dolly->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('dollies', ['id' => $dolly->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : un chariot d'une autre organisation répond 404 sur
     * show, update et destroy (jamais 403).
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $dolly = $this->makeDolly($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/dollies/{$dolly->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/dollies/{$dolly->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/dollies/{$dolly->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('dollies', ['id' => $dolly->id]);
    }
}
