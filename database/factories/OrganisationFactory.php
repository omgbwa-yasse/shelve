<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organisation>
 */
class OrganisationFactory extends Factory
{
    protected $model = Organisation::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('ORG##')),
            'name' => $this->faker->company(),
            'parent_id' => null,
        ];
    }
}
