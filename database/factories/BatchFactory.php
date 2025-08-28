<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('BCH###')),
            'name' => 'Batch '.$this->faker->unique()->numerify('###'),
            'organisation_holder_id' => Organisation::factory(),
        ];
    }
}
