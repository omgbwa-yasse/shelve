<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SlipStatus;
use Illuminate\Support\Facades\DB;

class SlipStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('slip_statuses')->delete();

        $statuses = [
            [
                'name' => 'Projects',
                'description' => 'Bordereau en projet, non encore finalisé',
            ],
            [
                'name' => 'Received',
                'description' => 'Bordereau reçu par l\'organisme destinataire',
            ],
            [
                'name' => 'Approved',
                'description' => 'Bordereau approuvé',
            ],
            [
                'name' => 'Integrated',
                'description' => 'Bordereau intégré dans le système d\'archivage',
            ],
        ];

        foreach ($statuses as $status) {
            SlipStatus::create($status);
        }

        $this->command->info('✓ ' . count($statuses) . ' statuts de bordereaux créés avec succès');
    }
}
