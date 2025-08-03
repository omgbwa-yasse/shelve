<?php

namespace Database\Seeders;

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

        // Ensure the activities are seeded first
        if (Activity::count() == 0) {
            $this->command->warn('Activities table is empty. Cannot seed communicability rules.');
            return;
        }

        // Disable foreign key checks to truncate the table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Communicability::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $activities = Activity::all();

        foreach ($activities as $activity) {
            // Assign a random rule from the predefined list
            $randomRule = $this->rules[array_rand($this->rules)];

            Communicability::create([
                'activity_id' => $activity->id,
                'name' => 'Règle de communicabilité pour ' . $activity->name,
                'description' => $randomRule['description'],
                'duration' => $randomRule['years'],
            ]);
        }

        $this->command->info('Successfully seeded communicability rules for ' . $activities->count() . ' activities.');
    }
}
