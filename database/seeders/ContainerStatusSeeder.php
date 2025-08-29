<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContainerStatus;

class ContainerStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Actif',
                'description' => 'Contenant en cours d\'utilisation',
                'creator_id' => 1,
            ],
            [
                'name' => 'Archivé',
                'description' => 'Contenant archivé et non modifiable',
                'creator_id' => 1,
            ],
            [
                'name' => 'En transit',
                'description' => 'Contenant en cours de transfert',
                'creator_id' => 1,
            ],
            [
                'name' => 'Temporaire',
                'description' => 'Contenant temporaire en attente de traitement',
                'creator_id' => 1,
            ],
            [
                'name' => 'Détruit',
                'description' => 'Contenant détruit selon les règles de conservation',
                'creator_id' => 1,
            ],
        ];

        foreach ($statuses as $status) {
            ContainerStatus::firstOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}
