<?php

namespace Tests\Feature;

use App\Models\MetadataDefinition;
use App\Models\RecordType;
use App\Models\RecordTypeMetadataProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Écran d'admin (settings.record-types.metadata.*) permettant d'attacher/détacher/
 * configurer des MetadataDefinition sur un RecordType — cœur du chantier « déplacer
 * les champs descriptifs de Record vers les métadonnées » (2026-08-05).
 */
class RecordTypeMetadataProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected RecordType $recordType;
    protected MetadataDefinition $definition;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->user = User::create([
            'name' => 'Test User ' . self::$counter,
            'email' => 'test-metadata-profile' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $role = Role::firstOrCreate(['name' => 'superadmin']);
        $this->user->roles()->attach($role->id);

        $this->recordType = RecordType::create([
            'code' => 'RT-' . self::$counter,
            'name' => 'Type de test ' . self::$counter,
            'is_container' => false,
            'is_active' => true,
        ]);

        $this->definition = MetadataDefinition::create([
            'name' => 'Numéro de dossier médical',
            'code' => 'medical_file_number_' . self::$counter,
            'data_type' => 'text',
            'searchable' => true,
            'active' => true,
            'is_system' => false,
            'sort_order' => 0,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_edit_screen_lists_available_and_attached_definitions()
    {
        $response = $this->actingAs($this->user)->get(route('settings.record-types.edit', $this->recordType));

        $response->assertOk();
        $response->assertSee($this->definition->name);
    }

    public function test_can_attach_a_metadata_definition_to_a_type()
    {
        $response = $this->actingAs($this->user)->post(route('settings.record-types.metadata.store', $this->recordType), [
            'metadata_definition_id' => $this->definition->id,
            'mandatory' => '1',
            'visible' => '1',
            'sort_order' => 5,
        ]);

        $response->assertRedirect(route('settings.record-types.edit', $this->recordType));

        $this->assertDatabaseHas('record_type_metadata_profiles', [
            'record_type_id' => $this->recordType->id,
            'metadata_definition_id' => $this->definition->id,
            'mandatory' => 1,
            'visible' => 1,
        ]);
    }

    public function test_cannot_attach_the_same_definition_twice()
    {
        RecordTypeMetadataProfile::create([
            'record_type_id' => $this->recordType->id,
            'metadata_definition_id' => $this->definition->id,
            'mandatory' => false,
            'visible' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('settings.record-types.metadata.store', $this->recordType), [
            'metadata_definition_id' => $this->definition->id,
        ]);

        $response->assertSessionHasErrors('metadata_definition_id');
        $this->assertEquals(1, RecordTypeMetadataProfile::where('record_type_id', $this->recordType->id)->count());
    }

    public function test_can_toggle_mandatory_and_visible_on_an_attached_profile()
    {
        $profile = RecordTypeMetadataProfile::create([
            'record_type_id' => $this->recordType->id,
            'metadata_definition_id' => $this->definition->id,
            'mandatory' => false,
            'visible' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('settings.record-types.metadata.update', [$this->recordType, $profile]), [
            'mandatory' => '1',
            'visible' => '0',
            'sort_order' => 9,
        ]);

        $response->assertRedirect();
        $profile->refresh();
        $this->assertTrue($profile->mandatory);
        $this->assertFalse($profile->visible);
        $this->assertEquals(9, $profile->sort_order);
    }

    public function test_can_detach_a_metadata_definition()
    {
        $profile = RecordTypeMetadataProfile::create([
            'record_type_id' => $this->recordType->id,
            'metadata_definition_id' => $this->definition->id,
            'mandatory' => false,
            'visible' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('settings.record-types.metadata.destroy', [$this->recordType, $profile]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('record_type_metadata_profiles', ['id' => $profile->id]);
    }
}
