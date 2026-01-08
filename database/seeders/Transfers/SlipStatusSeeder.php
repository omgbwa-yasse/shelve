<?php

namespace Database\Seeders\Transfers;

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
                'description' => 'Bordereau en projet, non encore finalisÃ©',
            ],
            [
                'name' => 'Received',
                'description' => 'Bordereau reÃ§u par l\'organisme destinataire',
            ],
            [
                'name' => 'Approved',
                'description' => 'Bordereau approuvÃ©',
            ],
            [
                'name' => 'Integrated',
                'description' => 'Bordereau intÃ©grÃ© dans le systÃ¨me d\'archivage',
            ],
        ];

        foreach ($statuses as $status) {
            SlipStatus::create($status);
        }

        $this->command->info('âœ“ ' . count($statuses) . ' statuts de bordereaux crÃ©Ã©s avec succÃ¨s');
    }
}

