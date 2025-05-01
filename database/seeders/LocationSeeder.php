<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Room types
        $roomTypes = [
            [
                'id' => 1,
                'name' => 'archives',
                'description' => 'Salle d\'archives',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'producer',
                'description' => 'Bureau du producteur',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Buildings
        $buildings = [
            [
                'id' => 1,
                'name' => 'Bâtiment Principal',
                'description' => 'Siège administratif',
                'creator_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Annexe Archives',
                'description' => 'Bâtiment de conservation des archives',
                'creator_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Centre Technique',
                'description' => 'Services techniques',
                'creator_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Floors
        $floors = [
            [
                'id' => 1,
                'name' => 'Rez-de-chaussée',
                'description' => 'Niveau 0',
                'building_id' => 1,
                'creator_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => '1er étage',
                'description' => 'Niveau 1',
                'building_id' => 1,
                'creator_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => '2ème étage',
                'description' => 'Niveau 2',
                'building_id' => 1,
                'creator_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Sous-sol',
                'description' => 'Niveau -1',
                'building_id' => 2,
                'creator_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Rez-de-chaussée',
                'description' => 'Niveau 0',
                'building_id' => 2,
                'creator_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'name' => '1er étage',
                'description' => 'Niveau 1',
                'building_id' => 2,
                'creator_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'name' => 'Rez-de-chaussée',
                'description' => 'Niveau 0',
                'building_id' => 3,
                'creator_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Rooms
        $rooms = [
            [
                'id' => 1,
                'code' => 'A001',
                'name' => 'Salle d\'archives historiques',
                'description' => 'Conservation des archives historiques',
                'floor_id' => 4,
                'creator_id' => 5,
                'type_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'A002',
                'name' => 'Salle d\'archives intermédiaires',
                'description' => 'Conservation des archives intermédiaires',
                'floor_id' => 5,
                'creator_id' => 5,
                'type_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'A003',
                'name' => 'Salle de tri',
                'description' => 'Salle de tri des documents',
                'floor_id' => 5,
                'creator_id' => 5,
                'type_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'A004',
                'name' => 'Salle de consultation',
                'description' => 'Consultation des archives',
                'floor_id' => 6,
                'creator_id' => 5,
                'type_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'code' => 'B001',
                'name' => 'Bureau Direction',
                'description' => 'Bureau de la direction',
                'floor_id' => 2,
                'creator_id' => 1,
                'type_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'code' => 'B002',
                'name' => 'Bureau Finances',
                'description' => 'Bureau du service financier',
                'floor_id' => 1,
                'creator_id' => 2,
                'type_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'code' => 'B003',
                'name' => 'Bureau RH',
                'description' => 'Bureau des ressources humaines',
                'floor_id' => 1,
                'creator_id' => 3,
                'type_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'code' => 'B004',
                'name' => 'Bureau Technique',
                'description' => 'Bureau du service technique',
                'floor_id' => 7,
                'creator_id' => 4,
                'type_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Organisation-Room relationships
        $organisationRooms = [
            ['room_id' => 1, 'organisation_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['room_id' => 2, 'organisation_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['room_id' => 3, 'organisation_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['room_id' => 4, 'organisation_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['room_id' => 5, 'organisation_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['room_id' => 6, 'organisation_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['room_id' => 7, 'organisation_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['room_id' => 8, 'organisation_id' => 4, 'created_at' => $now, 'updated_at' => $now],
        ];

        // Shelves
        $shelves = [
            [
                'id' => 1,
                'code' => 'E001',
                'observation' => 'Rayonnage métallique fixe',
                'face' => 5,
                'ear' => 3,
                'shelf' => 7,
                'shelf_length' => 100,
                'room_id' => 1,
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'E002',
                'observation' => 'Rayonnage métallique mobile',
                'face' => 6,
                'ear' => 2,
                'shelf' => 6,
                'shelf_length' => 120,
                'room_id' => 1,
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'E003',
                'observation' => 'Rayonnage métallique fixe',
                'face' => 4,
                'ear' => 2,
                'shelf' => 6,
                'shelf_length' => 90,
                'room_id' => 2,
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'E004',
                'observation' => 'Rayonnage métallique mobile',
                'face' => 8,
                'ear' => 2,
                'shelf' => 5,
                'shelf_length' => 100,
                'room_id' => 2,
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('room_types')->insert($roomTypes);
        DB::table('buildings')->insert($buildings);
        DB::table('floors')->insert($floors);
        DB::table('rooms')->insert($rooms);
        DB::table('organisation_room')->insert($organisationRooms);
        DB::table('shelves')->insert($shelves);
    }
}
