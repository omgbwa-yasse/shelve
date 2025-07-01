<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaskType;
use App\Models\Activity;

class TaskTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a default activity (assuming at least one exists)
        $activity = Activity::first();

        if (!$activity) {
            // Create a default activity if none exists
            $activity = Activity::create([
                'code' => 'DEFAULT',
                'name' => 'Default Activity',
            ]);
        }

        // Define task types
        $taskTypes = [
            [
                'name' => 'Archiving',
                'description' => 'Tasks related to archiving documents',
                'color' => '#007bff',
                'activity_id' => $activity->id,
            ],
            [
                'name' => 'Classification',
                'description' => 'Tasks related to classifying documents',
                'color' => '#28a745',
                'activity_id' => $activity->id,
            ],
            [
                'name' => 'Review',
                'description' => 'Tasks related to reviewing documents',
                'color' => '#ffc107',
                'activity_id' => $activity->id,
            ],
            [
                'name' => 'Validation',
                'description' => 'Tasks related to validating documents',
                'color' => '#dc3545',
                'activity_id' => $activity->id,
            ],
            [
                'name' => 'Research',
                'description' => 'Tasks related to researching information',
                'color' => '#6f42c1',
                'activity_id' => $activity->id,
            ],
        ];

        foreach ($taskTypes as $typeData) {
            TaskType::create($typeData);
        }
    }
}
