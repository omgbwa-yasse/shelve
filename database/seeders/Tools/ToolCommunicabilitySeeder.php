<?php

namespace Database\Seeders\Tools;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Communicability;
use Illuminate\Support\Facades\DB;

class ToolCommunicabilitySeeder extends Seeder
{
    /**
     * The predefined communicability rules.
     *
     * @var array
     */
    protected $rules = [
        [
            'years' => 2,
            'description' => 'Conservation de 2 ans dans les bureaux des services producteurs.',
        ],
        [
            'years' => 3,
            'description' => 'Conservation de 3 ans dans les bureaux des services producteurs.',
        ],
        [
            'years' => 5,
            'description' => 'Conservation de 5 ans dans les bureaux des services producteurs.',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding communicability rules...');

        // Disable foreign key checks to truncate the table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Communicability::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create generic communicability rules
        foreach ($this->rules as $index => $rule) {
            Communicability::create([
                'code' => 'COMM' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => 'RÃ¨gle de communicabilitÃ© ' . ($index + 1) . ' - ' . $rule['years'] . ' ans',
                'description' => $rule['description'],
                'duration' => $rule['years'],
            ]);
        }

        $this->command->info('Successfully seeded ' . count($this->rules) . ' communicability rules.');
    }
}

