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
        $admin = \App\Models\User::where('email', 'superadmin@example.com')->first() 
                 ?? \App\Models\User::first();
        $userId = $admin ? $admin->id : 1;

        $statuses = [
            [
                'name' => 'Actif',
                'description' => 'Contenant en cours d\'utilisation',
                'creator_id' => $userId,
            ],
            [
                'name' => 'Archivé',
                'description' => 'Contenant archivé et non modifiable',
                'creator_id' => $userId,
            ],
            [
                'name' => 'En transit',
                'description' => 'Contenant en cours de transfert',
                'creator_id' => $userId,
            ],
            [
                'name' => 'Temporaire',
                'description' => 'Contenant temporaire en attente de traitement',
                'creator_id' => $userId,
            ],
            [
                'name' => 'Détruit',
                'description' => 'Contenant détruit selon les règles de conservation',
                'creator_id' => $userId,
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

