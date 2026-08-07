<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D02 — notices (org-scopées par `organisation_id`, R03). Portage finalisé le 2026-08-05.
 */
class RecordApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['records'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeRecord(Organisation $org = null, array $extra = []): Record
    {
        $org = $org ?? $this->organisation;
        $level = RecordLevel::first() ?? RecordLevel::create(['name' => 'Niveau test']);
        $status = RecordStatus::first() ?? RecordStatus::create(['name' => 'Brouillon']);

        return Record::create([
            'code' => 'R' . substr(uniqid(), -8),
            'name' => 'Notice test',
            'level_id' => $level->id,
            'status_id' => $status->id,
            'access_level' => 'internal',
            'organisation_id' => $org->id,
            'creator_id' => $this->user->id,
            'version_number' => 1,
            'is_current_version' => true,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/records')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/records')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_notices_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeRecord();                 // dans mon org
        $this->makeRecord($orgEtrangere);    // dans une autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/records')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $record = $this->makeRecord();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $record->code)
            ->assertJsonPath('data.is_container', false)
            ->assertJsonPath('data.is_current_version', true);
    }

    public function test_store_cree_la_notice_avec_org_et_creator_de_l_agent(): void
    {
        $level = RecordLevel::first() ?? RecordLevel::create(['name' => 'Niveau test']);
        $status = RecordStatus::first() ?? RecordStatus::create(['name' => 'Brouillon']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/records', [
                'code' => 'R-NEW-001',
                'name' => 'Notice créée',
                'level_id' => $level->id,
                'status_id' => $status->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Notice créée')
            ->assertJsonPath('data.organisation_id', $this->organisation->id)
            ->assertJsonPath('data.creator_id', $this->user->id)
            ->assertJsonPath('data.is_current_version', true);

        $response->assertHeader('Location', "/api/v1/records/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/records', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_update_modifie_la_notice(): void
    {
        $record = $this->makeRecord();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/records/{$record->id}", ['name' => 'Notice renommée'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Notice renommée');
    }

    public function test_destroy_supprime_la_notice(): void
    {
        $record = $this->makeRecord();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('records', ['id' => $record->id]);
    }

    // --- Libellés des relations (2026-08-05) ---------------------------------

    public function test_show_expose_les_libelles_des_relations_incluses(): void
    {
        $level = RecordLevel::first() ?? RecordLevel::create(['name' => 'Niveau test']);
        $status = RecordStatus::first() ?? RecordStatus::create(['name' => 'Brouillon']);
        $record = $this->makeRecord(null, ['level_id' => $level->id, 'status_id' => $status->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}")
            ->assertOk()
            ->assertJsonPath('data.level.name', $level->name)
            ->assertJsonPath('data.status.name', $status->name);
    }

    public function test_index_expose_le_libelle_de_type(): void
    {
        $type = \App\Models\RecordType::first() ?? \App\Models\RecordType::create(['code' => 'T' . uniqid(), 'name' => 'Type test']);
        $this->makeRecord(null, ['type_id' => $type->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/records')
            ->assertOk()
            ->assertJsonPath('data.0.type.name', $type->name);
    }

    public function test_metadata_fields_retourne_le_schema_visible_du_type_avec_valeur(): void
    {
        $type = \App\Models\RecordType::create(['code' => 'T' . uniqid(), 'name' => 'Type avec métadonnées']);
        $definition = \App\Models\MetadataDefinition::create([
            'code' => 'note_' . uniqid(),
            'name' => 'Note',
            'data_type' => 'text',
            'created_by' => $this->user->id,
        ]);
        \App\Models\RecordTypeMetadataProfile::create([
            'record_type_id' => $type->id,
            'metadata_definition_id' => $definition->id,
            'mandatory' => true,
            'visible' => true,
            'readonly' => false,
            'sort_order' => 1,
        ]);

        $record = $this->makeRecord(null, ['type_id' => $type->id]);
        $record->setMultipleMetadata([$definition->code => 'Valeur de test']);
        $record->save();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/metadata-fields")
            ->assertOk();

        $this->assertEquals($definition->code, $response->json('data.0.code'));
        $this->assertEquals('Valeur de test', $response->json('data.0.value'));
        $this->assertTrue($response->json('data.0.required'));
    }

    public function test_record_type_metadata_fields_retourne_le_schema_sans_notice(): void
    {
        $this->grantD01Permissions($this->user, ['record_type'], ['view']);
        $type = \App\Models\RecordType::create(['code' => 'T' . uniqid(), 'name' => 'Type sans notice']);
        $definition = \App\Models\MetadataDefinition::create([
            'code' => 'ref_' . uniqid(),
            'name' => 'Référence',
            'data_type' => 'text',
            'created_by' => $this->user->id,
        ]);
        \App\Models\RecordTypeMetadataProfile::create([
            'record_type_id' => $type->id,
            'metadata_definition_id' => $definition->id,
            'mandatory' => false,
            'visible' => true,
            'readonly' => false,
            'sort_order' => 1,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/record-types/{$type->id}/metadata-fields")
            ->assertOk()
            ->assertJsonPath('data.0.code', $definition->code)
            ->assertJsonPath('data.0.required', false);
    }

    // --- Référentiels de sélection (2026-08-05) ------------------------------

    public function test_record_levels_index_liste_les_niveaux(): void
    {
        RecordLevel::firstOrCreate(['name' => 'Fonds']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/record-levels')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name']]]);
    }

    public function test_record_confidentialities_index_liste_les_niveaux(): void
    {
        \App\Models\RecordConfidentiality::firstOrCreate(['code' => 'PUB'], ['name' => 'Public']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/record-confidentialities')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'code', 'name']]]);
    }

    // --- Corbeille : trash / restore / force-delete (2026-08-05) ------------

    public function test_trash_liste_uniquement_les_notices_supprimees_de_l_organisation(): void
    {
        $active = $this->makeRecord();
        $deleted = $this->makeRecord();
        $deleted->delete();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/records-trash')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals($deleted->id, $response->json('data.0.id'));
        $this->assertNotEquals($active->id, $response->json('data.0.id'));
    }

    public function test_restore_sort_la_notice_de_la_corbeille(): void
    {
        $record = $this->makeRecord();
        $record->delete();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.id', $record->id);

        $this->assertDatabaseHas('records', ['id' => $record->id, 'deleted_at' => null]);
    }

    public function test_force_delete_supprime_definitivement(): void
    {
        $this->grantD01Permissions($this->user, self::PERMISSIONS, ['force_delete']);
        $record = $this->makeRecord();
        $record->delete();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}/force")
            ->assertNoContent();

        $this->assertDatabaseMissing('records', ['id' => $record->id]);
    }

    public function test_force_delete_sans_permission_dediee_est_refuse(): void
    {
        $record = $this->makeRecord();
        $record->delete();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}/force")
            ->assertStatus(403);

        $this->assertDatabaseHas('records', ['id' => $record->id]);
    }

    public function test_restore_et_force_delete_isoles_par_organisation(): void
    {
        $this->grantD01Permissions($this->user, self::PERMISSIONS, ['force_delete']);
        $orgEtrangere = Organisation::factory()->create();
        $record = $this->makeRecord($orgEtrangere);
        $record->delete();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/restore")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}/force")
            ->assertStatus(404);
    }

    /**
     * ⚠️ Cœur du risque R03 : une notice d'une autre organisation doit renvoyer 404
     * (jamais 403) sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $record = $this->makeRecord($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/records/{$record->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('records', ['id' => $record->id]);
    }
}
