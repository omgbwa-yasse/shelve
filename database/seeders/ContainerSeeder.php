<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContainerSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Container types
        $containerTypes = [
            [
                'id' => 1,
                'name' => 'Boîte archive standard',
                'description' => 'Boîte d\'archive standard 10cm',
            ],
            [
                'id' => 2,
                'name' => 'Boîte archive large',
                'description' => 'Boîte d\'archive large 15cm',
            ],
            [
                'id' => 3,
                'name' => 'Boîte format spécial',
                'description' => 'Boîte pour documents de grand format',
            ],
            [
                'id' => 4,
                'name' => 'Conteneur tube',
                'description' => 'Tube pour plans et documents roulés',
            ],
            [
                'id' => 5,
                'name' => 'Classeur',
                'description' => 'Classeur à levier',
            ],
        ];

        // Container properties
        $containerProperties = [
            [
                'id' => 1,
                'name' => 'Boîte standard S',
                'width' => 10,
                'length' => 25,
                'depth' => 33,
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Boîte standard M',
                'width' => 10,
                'length' => 32,
                'depth' => 25,
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Boîte standard L',
                'width' => 15,
                'length' => 25,
                'depth' => 33,
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Boîte grand format',
                'width' => 10,
                'length' => 37,
                'depth' => 55,
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Tube plan',
                'width' => 15,
                'length' => 15,
                'depth' => 100,
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Container statuses
        $containerStatuses = [
            [
                'id' => 1,
                'name' => 'Disponible',
                'description' => 'Conteneur disponible pour utilisation',
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'En cours d\'utilisation',
                'description' => 'Conteneur partiellement rempli',
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Complet',
                'description' => 'Conteneur entièrement rempli',
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'En réserve',
                'description' => 'Conteneur en attente d\'utilisation',
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Hors service',
                'description' => 'Conteneur endommagé ou obsolète',
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Containers
        $containers = [];

        // Generate 20 containers for each shelf
        for ($shelfId = 1; $shelfId <= 4; $shelfId++) {
            for ($i = 1; $i <= 20; $i++) {
                $statusId = $i <= 15 ? 3 : ($i <= 18 ? 2 : 1); // Most are complete, some in progress, few available
                $propertyId = rand(1, 5);

                $containers[] = [
                    'id' => ($shelfId - 1) * 20 + $i,
                    'code' => 'CON-' . $shelfId . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'shelve_id' => $shelfId,
                    'status_id' => $statusId,
                    'property_id' => $propertyId,
                    'creator_id' => 5,
                    'creator_organisation_id' => 5,
                    'is_archived' => ($statusId == 3),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('container_types')->insert($containerTypes);
        DB::table('container_properties')->insert($containerProperties);
        DB::table('container_statuses')->insert($containerStatuses);
        DB::table('containers')->insert($containers);
    }
}
