<?php

namespace Tests\Feature;

use App\Models\Container;
use App\Models\Record;
use App\Models\RecordMedium;
use App\Models\RecordSupport;
use App\Models\RecordType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\WithTestRecords;
use Tests\TestCase;

/**
 * Étape 6 — Papier vs numérique : mesure linéaire & capacité des contenants.
 */
class ContainerCapacityTest extends TestCase
{
    use RefreshDatabase, WithTestRecords;

    protected User $user;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->user = User::create([
            'name' => 'Cap ' . self::$counter,
            'email' => 'cap-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $this->user->roles()->attach(Role::firstOrCreate(['name' => 'superadmin'])->id);
    }

    public function test_remaining_space_is_capacity_minus_used(): void
    {
        $container = $this->testContainer(100);
        $type = RecordType::create(['code' => 'RT-CAP-' . self::$counter, 'name' => 'Type', 'is_active' => true, 'is_container' => false]);
        $support = RecordSupport::create(['name' => 'Papier']);

        $record = Record::create([
            'code' => 'RC-CAP-' . self::$counter,
            'name' => 'Notice',
            'type_id' => $type->id,
            'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id,
            'creator_id' => $this->user->id,
        ]);

        RecordMedium::create([
            'record_id' => $record->id,
            'support_id' => $support->id,
            'container_id' => $container->id,
            'linear_measure_cm' => 35.5,
        ]);

        $this->assertEquals(35.5, $container->usedLinearMeasureCm());
        $this->assertEquals(64.5, $container->remainingSpaceCm());
    }

    public function test_remaining_space_is_null_when_no_capacity(): void
    {
        $container = $this->testContainer(null);

        $this->assertNull($container->remainingSpaceCm());
    }

    public function test_finalize_medium_promotes_draft_to_final(): void
    {
        $type = RecordType::create(['code' => 'RT-FIN-' . self::$counter, 'name' => 'Type', 'is_active' => true, 'is_container' => false]);
        $support = RecordSupport::create(['name' => 'Papier']);

        $record = Record::create([
            'code' => 'RC-FIN-' . self::$counter,
            'name' => 'Notice',
            'type_id' => $type->id,
            'level_id' => $this->testLevel()->id,
            'status_id' => $this->testStatus()->id,
            'organisation_id' => $this->testOrganisation()->id,
            'creator_id' => $this->user->id,
        ]);

        $medium = RecordMedium::create([
            'record_id' => $record->id,
            'support_id' => $support->id,
            'status' => RecordMedium::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('records.mediums.finalize', [$record, $medium]));

        $response->assertRedirect(route('records.show', $record));
        $this->assertEquals(RecordMedium::STATUS_FINAL, $medium->fresh()->status);
    }
}
