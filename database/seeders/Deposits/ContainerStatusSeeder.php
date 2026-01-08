<?php

namespace Database\Seeders\Deposits;

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
                'name' => 'ArchivÃ©',
                'description' => 'Contenant archivÃ© et non modifiable',
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
                'name' => 'DÃ©truit',
                'description' => 'Contenant dÃ©truit selon les rÃ¨gles de conservation',
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

