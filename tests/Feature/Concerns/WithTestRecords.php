<?php

namespace Tests\Feature\Concerns;

use App\Models\Building;
use App\Models\Container;
use App\Models\ContainerProperty;
use App\Models\ContainerStatus;
use App\Models\Floor;
use App\Models\Organisation;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\Room;
use App\Models\Shelf;

/**
 * Helper de création de notices de test : le schéma exige `records.level_id`,
 * `records.status_id` et `records.organisation_id` (NOT NULL, FK). Fournit aussi
 * la chaîne bâtiment → étagère nécessaire aux contenants.
 */
trait WithTestRecords
{
    protected ?RecordLevel $testLevel = null;

    protected ?RecordStatus $testStatus = null;

    protected ?Organisation $testOrganisation = null;

    protected ?Shelf $testShelf = null;

    protected function testLevel(): RecordLevel
    {
        if (!$this->testLevel) {
            $this->testLevel = RecordLevel::firstOrCreate(['name' => 'Niveau de test']);
        }

        return $this->testLevel;
    }

    protected function testStatus(): RecordStatus
    {
        if (!$this->testStatus) {
            $this->testStatus = RecordStatus::firstOrCreate(['name' => 'Statut de test']);
        }

        return $this->testStatus;
    }

    protected function testOrganisation(): Organisation
    {
        if (!$this->testOrganisation) {
            $this->testOrganisation = Organisation::firstOrCreate(
                ['code' => 'ORG-TEST'],
                ['name' => 'Organisation de test']
            );
        }

        return $this->testOrganisation;
    }

    protected function testShelf(): Shelf
    {
        if (!$this->testShelf) {
            $creatorId = auth()->id() ?? \App\Models\User::query()->value('id') ?? 1;

            $building = Building::firstOrCreate(
                ['name' => 'Bâtiment de test'],
                ['visibility' => 'private', 'creator_id' => $creatorId]
            );

            $floor = Floor::firstOrCreate(
                ['name' => 'Étage de test'],
                ['building_id' => $building->id, 'creator_id' => $creatorId]
            );

            $room = Room::firstOrCreate(
                ['code' => 'SALLE-TEST'],
                [
                    'name' => 'Salle de test',
                    'floor_id' => $floor->id,
                    'visibility' => 'inherit',
                    'type' => 'archives',
                    'creator_id' => $creatorId,
                ]
            );

            $this->testShelf = Shelf::firstOrCreate(
                ['code' => 'ETAGERE-TEST'],
                [
                    'face' => 1,
                    'ear' => 1,
                    'shelf' => 1,
                    'shelf_length' => 100,
                    'room_id' => $room->id,
                    'creator_id' => $creatorId,
                ]
            );
        }

        return $this->testShelf;
    }

    protected function testContainer(?float $capacityCm = null): Container
    {
        $shelf = $this->testShelf();

        $container = Container::where('code', 'BOITE-TEST-' . ($capacityCm === null ? 'NC' : $capacityCm))->first();

        if (!$container) {
            $creatorId = auth()->id() ?? \App\Models\User::query()->value('id') ?? 1;
            $status = ContainerStatus::firstOrCreate(
                ['name' => 'Statut contenant'],
                ['creator_id' => $creatorId]
            );
            $property = ContainerProperty::firstOrCreate(
                ['name' => 'Propriété de test'],
                ['width' => 10, 'length' => 10, 'depth' => 10, 'creator_id' => $creatorId]
            );

            $container = Container::create([
                'code' => 'BOITE-TEST-' . ($capacityCm === null ? 'NC' : (string) $capacityCm),
                'shelve_id' => $shelf->id,
                'status_id' => $status->id,
                'property_id' => $property->id,
                'capacity_cm' => $capacityCm,
                'creator_id' => $creatorId,
                'creator_organisation_id' => $this->testOrganisation()->id,
            ]);
        }

        return $container;
    }
}
