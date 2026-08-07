<?php

namespace Tests\Feature;

use App\Models\MetadataDefinition;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordType;
use App\Models\RecordTypeMetadataProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RecordController (web) n'avait aucun test avant ce chantier. Couvre le
 * create/store/edit/update/show/index avec le système de métadonnées dynamique
 * (déplacement des anciens champs descriptifs figés, 2026-08-05).
 */
class RecordControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organisation $organisation;
    protected RecordLevel $level;
    protected RecordStatus $status;
    protected RecordType $type;
    protected RecordType $containerType;
    protected Record $parentRecord;
    protected MetadataDefinition $contentDefinition;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->user = User::create([
            'name' => 'Test User ' . self::$counter,
            'email' => 'test-record-controller' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $role = Role::firstOrCreate(['name' => 'superadmin']);
        $this->user->roles()->attach($role->id);

        $this->organisation = Organisation::create([
            'code' => 'ORG-RC-' . self::$counter,
            'name' => 'Test Organisation ' . self::$counter,
        ]);

        $this->level = RecordLevel::create(['code' => 'LVL-RC-' . self::$counter, 'name' => 'Niveau ' . self::$counter]);
        $this->status = RecordStatus::create(['name' => 'Statut RC ' . self::$counter]);

        $this->type = RecordType::create([
            'code' => 'RT-RC-' . self::$counter,
            'name' => 'Type de test ' . self::$counter,
            'is_container' => false,
            'is_active' => true,
        ]);

        $this->containerType = RecordType::create([
            'code' => 'RT-RC-DOSSIER-' . self::$counter,
            'name' => 'Dossier de test ' . self::$counter,
            'is_container' => true,
            'is_active' => true,
        ]);

        $this->parentRecord = Record::create([
            'code' => 'REC-PARENT-' . self::$counter,
            'name' => 'Dossier parent ' . self::$counter,
            'level_id' => $this->level->id,
            'status_id' => $this->status->id,
            'type_id' => $this->containerType->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'access_level' => 'internal',
            'version_number' => 1,
            'is_current_version' => true,
        ]);

        $this->contentDefinition = MetadataDefinition::create([
            'name' => 'Contenu',
            'code' => 'content_rc_' . self::$counter,
            'data_type' => 'textarea',
            'searchable' => true,
            'active' => true,
            'is_system' => true,
            'sort_order' => 0,
            'created_by' => $this->user->id,
        ]);

        RecordTypeMetadataProfile::create([
            'record_type_id' => $this->type->id,
            'metadata_definition_id' => $this->contentDefinition->id,
            'mandatory' => true,
            'visible' => true,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_create_form_loads()
    {
        $response = $this->actingAs($this->user)->get(route('records.create'));

        $response->assertOk();
    }

    public function test_store_requires_mandatory_metadata_for_the_selected_type()
    {
        $response = $this->actingAs($this->user)->post(route('records.store'), [
            'name' => 'Notice sans contenu',
            'type_id' => $this->type->id,
            'parent_id' => $this->parentRecord->id,
            'organisation_id' => $this->organisation->id,
            // 'metadata' volontairement absent : content_rc_N est obligatoire pour ce type.
        ]);

        $response->assertSessionHasErrors('content_rc_' . self::$counter);
        $this->assertDatabaseMissing('records', ['name' => 'Notice sans contenu']);
    }

    public function test_store_creates_record_with_metadata()
    {
        $response = $this->actingAs($this->user)->post(route('records.store'), [
            'name' => 'Notice avec contenu',
            'type_id' => $this->type->id,
            'parent_id' => $this->parentRecord->id,
            'organisation_id' => $this->organisation->id,
            'metadata' => [$this->contentDefinition->code => 'Un contenu de test'],
        ]);

        $response->assertRedirect();

        $record = Record::where('name', 'Notice avec contenu')->first();
        $this->assertNotNull($record);
        $this->assertEquals('Un contenu de test', $record->getMetadataValue($this->contentDefinition->code));
    }

    public function test_show_displays_metadata_values()
    {
        $record = Record::create([
            'code' => 'REC-SHOW-' . self::$counter,
            'name' => 'Notice affichée',
            'level_id' => $this->level->id,
            'status_id' => $this->status->id,
            'type_id' => $this->type->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'access_level' => 'internal',
            'version_number' => 1,
            'is_current_version' => true,
        ]);
        $record->setMultipleMetadata([$this->contentDefinition->code => 'Contenu visible sur la fiche']);
        $record->save();

        $response = $this->actingAs($this->user)->get(route('records.show', $record));

        $response->assertOk();
        $response->assertSee('Contenu visible sur la fiche');
    }

    public function test_update_modifies_metadata()
    {
        $record = Record::create([
            'code' => 'REC-UPD-' . self::$counter,
            'name' => 'Notice à modifier',
            'level_id' => $this->level->id,
            'status_id' => $this->status->id,
            'type_id' => $this->type->id,
            'parent_id' => $this->parentRecord->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'access_level' => 'internal',
            'version_number' => 1,
            'is_current_version' => true,
        ]);
        $record->setMultipleMetadata([$this->contentDefinition->code => 'Avant']);
        $record->save();

        $response = $this->actingAs($this->user)->put(route('records.update', $record), [
            'name' => $record->name,
            'parent_id' => $this->parentRecord->id,
            'organisation_id' => $this->organisation->id,
            'metadata' => [$this->contentDefinition->code => 'Après'],
        ]);

        $response->assertRedirect();
        $this->assertEquals('Après', $record->fresh()->getMetadataValue($this->contentDefinition->code));
    }

    public function test_index_keyword_search_does_not_error_on_metadata_json()
    {
        $record = Record::create([
            'code' => 'REC-IDX-' . self::$counter,
            'name' => 'Notice recherchable',
            'level_id' => $this->level->id,
            'status_id' => $this->status->id,
            'type_id' => $this->type->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'access_level' => 'internal',
            'version_number' => 1,
            'is_current_version' => true,
        ]);
        $record->setMultipleMetadata([$this->contentDefinition->code => 'Terme unique xyzzy']);
        $record->save();

        $response = $this->actingAs($this->user)->get(route('records.index', ['keyword_filter' => 'xyzzy']));

        $response->assertOk();
        $response->assertSee('Notice recherchable');
    }
}
