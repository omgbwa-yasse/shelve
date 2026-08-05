<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre le portage de RecordChildController et SEDAExportController::exportRecord
 * du modèle legacy RecordPhysical vers le modèle unifié Record (finalisation de la
 * migration, 2026-08-05). Avant ce portage, RecordChildController::create() était
 * cassé de façon fatale (type hint `INT $id`, classe `record` en minuscule).
 */
class RecordChildAndSedaExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organisation $organisation;
    protected Activity $activity;
    protected RecordStatus $status;
    protected RecordLevel $level;
    protected Record $parent;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->user = User::create([
            'name' => 'Test User ' . self::$counter,
            'email' => 'test-child-seda' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);

        $this->organisation = Organisation::create([
            'code' => 'ORG-CS-' . self::$counter,
            'name' => 'Test Organisation ' . self::$counter,
        ]);

        $this->status = RecordStatus::create(['name' => 'Statut ' . self::$counter]);
        $this->level = RecordLevel::create(['code' => 'LVL-CS-' . self::$counter, 'name' => 'Niveau ' . self::$counter]);

        $this->activity = Activity::create([
            'code' => 'ACT-CS-' . str_pad(self::$counter, 3, '0', STR_PAD_LEFT),
            'name' => 'Activité ' . self::$counter,
        ]);
        $this->activity->organisations()->attach($this->organisation->id, ['creator_id' => $this->user->id]);

        $this->parent = Record::create([
            'code' => 'PARENT-' . self::$counter,
            'name' => 'Dossier parent',
            'level_id' => $this->level->id,
            'status_id' => $this->status->id,
            'activity_id' => $this->activity->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'access_level' => 'internal',
            'version_number' => 1,
            'is_current_version' => true,
        ]);
    }

    public function test_child_index_loads_for_unified_record_parent()
    {
        $response = $this->actingAs($this->user)->get(route('record-child.index', $this->parent));

        $response->assertOk();
        $response->assertSee($this->parent->code);
    }

    public function test_child_create_form_no_longer_fatals()
    {
        $response = $this->actingAs($this->user)->get(route('record-child.create', $this->parent));

        $response->assertOk();
    }

    public function test_child_store_creates_a_unified_record_attached_to_parent()
    {
        $response = $this->actingAs($this->user)->post(route('record-child.store', $this->parent), [
            'name' => 'Notice fille',
            'code' => 'CHILD-' . self::$counter,
        ]);

        $response->assertRedirect();

        $child = Record::where('code', 'CHILD-' . self::$counter)->first();
        $this->assertNotNull($child);
        $this->assertEquals($this->parent->id, $child->parent_id);
        $this->assertEquals($this->organisation->id, $child->organisation_id);
    }

    public function test_child_store_generates_a_code_when_missing()
    {
        $response = $this->actingAs($this->user)->post(route('record-child.store', $this->parent), [
            'name' => 'Notice fille sans code',
        ]);

        $response->assertRedirect();

        $child = Record::where('name', 'Notice fille sans code')->first();
        $this->assertNotNull($child);
        $this->assertNotEmpty($child->code);
    }

    public function test_seda_export_record_works_on_unified_record()
    {
        $response = $this->actingAs($this->user)->get(route('records.export.seda', $this->parent));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertStringContainsString($this->parent->code, $response->getContent());
    }
}
