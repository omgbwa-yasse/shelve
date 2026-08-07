<?php

namespace Tests\Feature;

use App\Models\MetadataDefinition;
use App\Models\Record;
use App\Models\RecordType;
use App\Models\ReferenceList;
use App\Models\ReferenceValue;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\WithTestRecords;
use Tests\TestCase;

/**
 * Étape 1 — Corbeille & suppression sécurisée : aucune suppression silencieuse
 * d'un RecordType/ReferenceValue encore utilisé, et restauration possible.
 */
class ReferenceListSafeguardTest extends TestCase
{
    use RefreshDatabase, WithTestRecords;

    protected User $user;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->user = User::create([
            'name' => 'Safeguard ' . self::$counter,
            'email' => 'safeguard-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $this->user->roles()->attach(Role::firstOrCreate(['name' => 'superadmin'])->id);
    }

    public function test_delete_value_blocked_when_used_by_a_record(): void
    {
        $list = ReferenceList::create(['name' => 'L' . self::$counter, 'code' => 'L' . self::$counter, 'created_by' => $this->user->id]);
        $value = ReferenceValue::create(['list_id' => $list->id, 'value' => 'Alpha', 'code' => 'A' . self::$counter, 'created_by' => $this->user->id]);

        $definition = MetadataDefinition::create([
            'name' => 'Champ lié',
            'code' => 'linked_field_' . self::$counter,
            'data_type' => 'reference_list',
            'reference_list_id' => $list->id,
            'active' => true,
            'searchable' => true,
            'is_system' => false,
            'created_by' => $this->user->id,
        ]);

        $type = RecordType::create(['code' => 'RT-SAFE-' . self::$counter, 'name' => 'Type safe', 'is_active' => true, 'is_container' => false]);
        Record::create([
            'code' => 'RC-SAFE-' . self::$counter,
            'name' => 'Notice avec valeur',
            'type_id' => $type->id,
            'metadata' => [$definition->code => $value->code],
            'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id,
            'creator_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('settings.reference-lists.values.destroy', [$list, $value]));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('reference_values', ['id' => $value->id]);
    }

    public function test_unused_inactive_values_can_be_purged(): void
    {
        $list = ReferenceList::create(['name' => 'Purge ' . self::$counter, 'code' => 'PURGE-' . self::$counter, 'created_by' => $this->user->id]);
        ReferenceValue::create(['list_id' => $list->id, 'value' => 'Vieux', 'code' => 'OLD-' . self::$counter, 'active' => false, 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->post(route('settings.reference-lists.purge-inactive', $list));

        $response->assertSessionHas('success');
        $this->assertSoftDeleted('reference_values', ['code' => 'OLD-' . self::$counter]);
    }

    public function test_destroy_reference_list_blocked_when_used_by_metadata(): void
    {
        $list = ReferenceList::create(['name' => 'Linked ' . self::$counter, 'code' => 'LINKED-' . self::$counter, 'created_by' => $this->user->id]);
        MetadataDefinition::create([
            'name' => 'Champ lié',
            'code' => 'linked_' . self::$counter,
            'data_type' => 'reference_list',
            'reference_list_id' => $list->id,
            'active' => true,
            'searchable' => true,
            'is_system' => false,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('settings.reference-lists.destroy', $list));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('reference_lists', ['id' => $list->id]);
    }

    public function test_restore_trashed_record(): void
    {
        $type = RecordType::create(['code' => 'RT-REST-' . self::$counter, 'name' => 'Type', 'is_active' => true, 'is_container' => false]);
        $record = Record::create([
            'code' => 'RC-REST-' . self::$counter,
            'name' => 'Notice à restaurer',
            'type_id' => $type->id,
            'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id,
            'creator_id' => $this->user->id,
        ]);

        $record->delete();
        $this->assertSoftDeleted('records', ['id' => $record->id]);

        $response = $this->actingAs($this->user)->post(route('records.restore', $record->id));

        $response->assertRedirect(route('records.trash'));
        $this->assertDatabaseHas('records', ['id' => $record->id, 'deleted_at' => null]);
    }

    public function test_trash_screen_lists_only_trashed_records(): void
    {
        $type = RecordType::create(['code' => 'RT-TR-' . self::$counter, 'name' => 'Type', 'is_active' => true, 'is_container' => false]);
        $deleted = Record::create(['code' => 'RC-TR-D-' . self::$counter, 'name' => 'Supprimée', 'type_id' => $type->id, 'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id, 'creator_id' => $this->user->id]);
        $deleted->delete();
        Record::create(['code' => 'RC-TR-A-' . self::$counter, 'name' => 'Active', 'type_id' => $type->id, 'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id, 'creator_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('records.trash'));

        $response->assertOk();
        $response->assertSee('Supprimée');
        $response->assertDontSee('Active');
    }
}
