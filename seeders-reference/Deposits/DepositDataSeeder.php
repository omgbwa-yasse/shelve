<?php

namespace Database\Seeders\Deposits;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Container;
use App\Models\ContainerProperty;
use App\Models\ContainerStatus;
use App\Models\Shelf;
use App\Models\User;
use App\Models\Organisation;

class DepositDataSeeder extends Seeder
{
    /**
     * Seed test data for the Deposits (Dépôts) module.
     * Creates container properties, container statuses, and containers on shelves.
     * Idempotent: uses firstOrCreate.
     */
    public function run(): void
    {
        $this->command->info('🏗️ Seeding Deposits module test data...');

        $user = User::first();
        $org = Organisation::first();
        $shelves = Shelf::take(5)->get();

        if (!$user || !$org) {
            $this->command->warn('⚠️  No users/organisations found. Run UserSeeder first.');
            return;
        }

        if ($shelves->isEmpty()) {
            $this->command->warn('⚠️  No shelves found. Run BuildingSeeder first.');
            return;
        }

        // --- 1. Container Properties ---
        $properties = [
            ['name' => 'Boîte standard',     'width' => 36.0, 'length' => 25.0, 'depth' => 12.0],
            ['name' => 'Boîte grande',        'width' => 42.0, 'length' => 30.0, 'depth' => 15.0],
            ['name' => 'Boîte à plan',        'width' => 90.0, 'length' => 65.0, 'depth' => 5.0],
            ['name' => 'Classeur vertical',   'width' => 30.0, 'length' => 25.0, 'depth' => 8.0],
            ['name' => 'Carton de déménagement', 'width' => 60.0, 'length' => 40.0, 'depth' => 40.0],
        ];

        $createdProps = [];
        foreach ($properties as $p) {
            $createdProps[] = ContainerProperty::firstOrCreate(
                ['name' => $p['name']],
                array_merge($p, ['creator_id' => $user->id])
            );
        }

        // --- 2. Container Statuses ---
        $statusDefs = [
            ['name' => 'Disponible',    'description' => 'Le conteneur est vide et disponible.'],
            ['name' => 'En usage',      'description' => 'Le conteneur est attribué et contient des documents.'],
            ['name' => 'Plein',         'description' => 'Le conteneur est plein, aucun ajout possible.'],
            ['name' => 'En réparation', 'description' => 'Le conteneur est endommagé et en cours de réparation.'],
            ['name' => 'Archivé',       'description' => 'Le conteneur est en dépôt longue durée.'],
        ];

        $createdStatuses = [];
        foreach ($statusDefs as $sd) {
            $createdStatuses[] = ContainerStatus::firstOrCreate(
                ['name' => $sd['name']],
                array_merge($sd, ['creator_id' => $user->id])
            );
        }

        // --- 3. Containers ---
        $containerDefs = [
            ['code' => 'CTN-2026-001', 'shelf_index' => 0, 'prop_index' => 0, 'status_index' => 1, 'archived' => false],
            ['code' => 'CTN-2026-002', 'shelf_index' => 0, 'prop_index' => 0, 'status_index' => 1, 'archived' => false],
            ['code' => 'CTN-2026-003', 'shelf_index' => 1, 'prop_index' => 1, 'status_index' => 2, 'archived' => false],
            ['code' => 'CTN-2026-004', 'shelf_index' => 1, 'prop_index' => 0, 'status_index' => 0, 'archived' => false],
            ['code' => 'CTN-2026-005', 'shelf_index' => 2, 'prop_index' => 2, 'status_index' => 1, 'archived' => false],
            ['code' => 'CTN-2026-006', 'shelf_index' => 2, 'prop_index' => 3, 'status_index' => 3, 'archived' => false],
            ['code' => 'CTN-2026-007', 'shelf_index' => 3 % $shelves->count(), 'prop_index' => 0, 'status_index' => 4, 'archived' => true],
            ['code' => 'CTN-2026-008', 'shelf_index' => 4 % $shelves->count(), 'prop_index' => 4, 'status_index' => 0, 'archived' => false],
            ['code' => 'CTN-2026-009', 'shelf_index' => 0, 'prop_index' => 1, 'status_index' => 1, 'archived' => false],
            ['code' => 'CTN-2026-010', 'shelf_index' => 1, 'prop_index' => 0, 'status_index' => 2, 'archived' => false],
        ];

        $createdContainers = [];
        foreach ($containerDefs as $cd) {
            $createdContainers[] = Container::firstOrCreate(
                ['code' => $cd['code']],
                [
                    'shelve_id' => $shelves[$cd['shelf_index']]->id,
                    'status_id' => $createdStatuses[$cd['status_index']]->id,
                    'property_id' => $createdProps[$cd['prop_index']]->id,
                    'creator_id' => $user->id,
                    'creator_organisation_id' => $org->id,
                    'is_archived' => $cd['archived'],
                ]
            );
        }

        // --- 4. Container ↔ Record associations (link first containers to existing records) ---
        $records = DB::table('record_physicals')->take(4)->get();
        if ($records->isNotEmpty() && count($createdContainers) >= 4) {
            foreach ($records as $i => $rec) {
                DB::table('record_physical_container')->updateOrInsert(
                    ['record_physical_id' => $rec->id, 'container_id' => $createdContainers[$i]->id],
                    [
                        'description' => "Conditionnement dans {$createdContainers[$i]->code}",
                        'creator_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('✅ Deposits: ' . count($createdProps) . ' properties, ' . count($createdStatuses) . ' statuses, ' . count($createdContainers) . ' containers seeded.');
    }
}
