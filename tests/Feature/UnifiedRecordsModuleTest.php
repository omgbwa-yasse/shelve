<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordType;
use App\Models\User;
use Tests\TestCase;

/**
 * Test HTTP du module unifié (Phase 5/6) : RecordController + vues records/*.
 * Utilise la base existante (données migrées), sans RefreshDatabase.
 */
class UnifiedRecordsModuleTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $org = Organisation::first() ?? Organisation::create(['code' => 'ORG-T', 'name' => 'Org Test']);

        $this->user = User::create([
            'name' => 'Unified Test User',
            'email' => 'unified-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
            'current_organisation_id' => $org->id,
        ]);

        $role = \App\Models\Role::firstOrCreate(['name' => 'superadmin']);
        $this->user->roles()->sync([$role->id]);
    }

    public function test_index_lists_unified_records(): void
    {
        $this->actingAs($this->user)
            ->get(route('records.index'))
            ->assertStatus(200)
            ->assertSee('Archives');
    }

    public function test_search_route_works(): void
    {
        $this->actingAs($this->user)
            ->get(route('records.search') . '?q=test')
            ->assertStatus(200);
    }

    public function test_show_renders_record_with_mediums(): void
    {
        $record = Record::currentVersion()->first();

        $this->assertNotNull($record, 'Aucune notice migrée en base');

        $this->actingAs($this->user)
            ->get(route('records.show', $record))
            ->assertStatus(200)
            ->assertSee($record->name)
            ->assertSee('Supports');
    }

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->user)
            ->get(route('records.create'))
            ->assertStatus(200)
            ->assertSee('Nouvelle notice');
    }

    public function test_store_creates_record(): void
    {
        $org = Organisation::first();

        $response = $this->actingAs($this->user)->post(route('records.store'), [
            'name' => 'Notice de test HTTP ' . time(),
            'organisation_id' => $org->id,
            'level_id' => RecordLevel::first()->id,
            'status_id' => RecordStatus::first()->id,
            'type_id' => RecordType::where('code', 'PAPER_RECORD')->value('id'),
            'metadata' => ['montant' => '100'],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('records', [
            'name' => 'Notice de test HTTP ' . time(),
        ]);
    }

    public function test_versions_page_renders(): void
    {
        $record = Record::currentVersion()->where('version_number', '>', 1)->first()
            ?? Record::currentVersion()->first();

        $this->actingAs($this->user)
            ->get(route('records.versions', $record))
            ->assertStatus(200);
    }
}
