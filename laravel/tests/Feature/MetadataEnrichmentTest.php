<?php

namespace Tests\Feature;

use App\Models\MetadataDefinition;
use App\Models\Record;
use App\Models\RecordType;
use App\Models\RecordTypeMetadataProfile;
use App\Models\Role;
use App\Models\User;
use App\Services\MetadataValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\WithTestRecords;
use Tests\TestCase;

/**
 * Étapes 3, 4, 5 — propriétés de métadonnées enrichies (max_length configurable),
 * métadonnées copiées (parent → enfant) et calculées, sécurité par rôle.
 */
class MetadataEnrichmentTest extends TestCase
{
    use RefreshDatabase, WithTestRecords;

    protected User $user;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->user = User::create([
            'name' => 'Meta ' . self::$counter,
            'email' => 'meta-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $this->user->roles()->attach(Role::firstOrCreate(['name' => 'superadmin'])->id);
    }

    protected function makeType(array $definitions): RecordType
    {
        $type = RecordType::create([
            'code' => 'RT-META-' . self::$counter,
            'name' => 'Type meta ' . self::$counter,
            'is_container' => false,
            'is_active' => true,
        ]);

        foreach ($definitions as $config) {
            $def = MetadataDefinition::create(array_merge([
                'name' => $config['name'],
                'code' => $config['code'],
                'data_type' => $config['data_type'] ?? 'text',
                'active' => true,
                'searchable' => true,
                'is_system' => false,
                'created_by' => $this->user->id,
            ], $config['attributes'] ?? []));

            RecordTypeMetadataProfile::create([
                'record_type_id' => $type->id,
                'metadata_definition_id' => $def->id,
                'visible' => true,
                'mandatory' => false,
                'restricted_to_roles' => $config['restricted_to_roles'] ?? null,
                'created_by' => $this->user->id,
            ]);
        }

        return $type;
    }

    protected function makeRecord(RecordType $type, array $metadata = [], ?int $parentId = null): Record
    {
        return Record::create([
            'code' => 'RC-META-' . self::$counter . '-' . (string) random_int(1, 999999),
            'name' => 'Notice ' . self::$counter,
            'type_id' => $type->id,
            'metadata' => $metadata,
            'parent_id' => $parentId,
            'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id,
            'creator_id' => $this->user->id,
        ]);
    }

    public function test_max_length_is_configurable(): void
    {
        $type = $this->makeType([
            ['name' => 'Titre', 'code' => 'titre', 'attributes' => ['max_length' => 10]],
        ]);

        $service = app(MetadataValidationService::class);

        $this->assertThrows(
            fn () => $service->validateRecordMetadata($type, ['titre' => str_repeat('a', 11)]),
            \Illuminate\Validation\ValidationException::class
        );

        $validated = $service->validateRecordMetadata($type, ['titre' => 'court']);

        $this->assertEquals('court', $validated['titre']);
    }

    public function test_copied_metadata_from_parent_is_applied_on_save(): void
    {
        $type = $this->makeType([
            ['name' => 'Cote', 'code' => 'cote', 'attributes' => ['copy_source_type' => 'parent', 'copy_source_field' => 'cote_source']],
        ]);

        $parent = $this->makeRecord($type, ['cote_source' => 'FR-2000-01']);
        $child = $this->makeRecord($type, ['cote_source' => null], $parent->id);

        $this->assertEquals('FR-2000-01', $child->getMetadataValue('cote'));
    }

    public function test_computed_metadata_is_recalculated_on_save(): void
    {
        $type = $this->makeType([
            ['name' => 'Code', 'code' => 'code_note', 'attributes' => []],
            ['name' => 'Cote complète', 'code' => 'cote_complete', 'attributes' => ['computed_template' => '$code_note-$name']],
        ]);

        $record = $this->makeRecord($type, ['code_note' => 'X12']);

        $this->assertEquals('X12-' . $record->name, $record->getMetadataValue('cote_complete'));

        $record->setMetadataValue('code_note', 'X99');
        $record->save();

        $this->assertEquals('X99-' . $record->name, $record->getMetadataValue('cote_complete'));
    }

    public function test_restricted_metadata_hidden_for_non_authorized_role(): void
    {
        $reader = User::create([
            'name' => 'Lecteur',
            'email' => 'reader-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $reader->roles()->attach(Role::firstOrCreate(['name' => 'reader'])->id);

        $type = $this->makeType([
            ['name' => 'Champ public', 'code' => 'pub', 'attributes' => []],
            ['name' => 'Champ secret', 'code' => 'secret', 'attributes' => [], 'restricted_to_roles' => ['superadmin']],
        ]);

        $record = $this->makeRecord($type, ['pub' => 'Visible', 'secret' => 'Confidentiel']);

        $this->actingAs($this->user); // superadmin
        $visible = collect($record->getVisibleMetadataFields())->pluck('code')->all();
        $this->assertContains('secret', $visible);

        $this->actingAs($reader);
        $visible = collect($record->getVisibleMetadataFields())->pluck('code')->all();
        $this->assertNotContains('secret', $visible);
        $this->assertContains('pub', $visible);

        // Indexation : le champ restreint est exclu du texte indexé
        $this->actingAs($reader);
        $this->assertStringNotContainsString('Confidentiel', $record->toSearchableArray()['metadata_text']);
    }
}
