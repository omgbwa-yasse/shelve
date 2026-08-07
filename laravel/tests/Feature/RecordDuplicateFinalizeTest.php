<?php

namespace Tests\Feature;

use App\Models\Keyword;
use App\Models\Record;
use App\Models\RecordType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\WithTestRecords;
use Tests\TestCase;

/**
 * Étape 9 — Duplication de notices & versions mineure/majeure.
 */
class RecordDuplicateFinalizeTest extends TestCase
{
    use RefreshDatabase, WithTestRecords;

    protected User $user;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->user = User::create([
            'name' => 'Dup ' . self::$counter,
            'email' => 'dup-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $this->user->roles()->attach(Role::firstOrCreate(['name' => 'superadmin'])->id);
    }

    public function test_duplicate_metadata_only(): void
    {
        $type = RecordType::create([
            'code' => 'RT-DUP-' . self::$counter,
            'name' => 'Type',
            'code_prefix' => 'DUP',
            'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
            'is_active' => true,
            'is_container' => false,
        ]);

        $record = Record::create([
            'code' => 'RC-DUP-' . self::$counter,
            'name' => 'Notice source',
            'type_id' => $type->id,
            'metadata' => ['contenu' => 'important'],
            'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id,
            'creator_id' => $this->user->id,
        ]);

        $copy = $record->duplicate();

        $this->assertNotEquals($record->id, $copy->id);
        $this->assertEquals('important', $copy->getMetadataValue('contenu'));
        $this->assertEquals(1, $copy->version_number);
        $this->assertEquals($record->parent_id, $copy->parent_id);
    }

    public function test_duplicate_copies_own_relations(): void
    {
        $type = RecordType::create([
            'code' => 'RT-DUPREL-' . self::$counter,
            'name' => 'Type',
            'is_active' => true,
            'is_container' => false,
        ]);

        $record = Record::create([
            'code' => 'RC-DUPREL-' . self::$counter,
            'name' => 'Notice source',
            'type_id' => $type->id,
            'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id,
            'creator_id' => $this->user->id,
        ]);

        $keyword = Keyword::firstOrCreate(['name' => 'mot-cle-duplication-test']);
        $record->keywords()->attach($keyword->id);

        $copy = $record->duplicate();

        $this->assertTrue($copy->keywords->contains($keyword));
    }

    public function test_duplicate_with_children_copies_tree(): void
    {
        $type = RecordType::create(['code' => 'RT-TREE-' . self::$counter, 'name' => 'Type', 'is_active' => true, 'is_container' => false]);

        $parent = Record::create(['code' => 'RC-P-' . self::$counter, 'name' => 'Parent', 'type_id' => $type->id, 'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id, 'creator_id' => $this->user->id]);
        $child = Record::create(['code' => 'RC-C-' . self::$counter, 'name' => 'Enfant', 'type_id' => $type->id, 'parent_id' => $parent->id, 'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id, 'creator_id' => $this->user->id]);

        $copy = $parent->duplicate(true);

        $this->assertEquals(1, $copy->children()->count());
        $this->assertNotEquals($child->id, $copy->children()->first()->id);
    }

    public function test_duplicate_via_route(): void
    {
        $type = RecordType::create([
            'code' => 'RT-ROUTE-' . self::$counter,
            'name' => 'Type',
            'code_prefix' => 'DUP',
            'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
            'is_active' => true,
            'is_container' => false,
        ]);

        $record = Record::create(['code' => 'RC-ROUTE-' . self::$counter, 'name' => 'Notice', 'type_id' => $type->id, 'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id, 'creator_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->post(route('records.duplicate', $record));

        $response->assertRedirect();
        $this->assertEquals(2, Record::count());
    }
}
