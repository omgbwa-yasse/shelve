<?php

namespace Database\Factories;

use App\Models\Dolly;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dolly>
 */
class DollyFactory extends Factory
{
    protected $model = Dolly::class;

    public function definition(): array
    {
        return [
            'name' => 'Dolly '.$this->faker->unique()->numerify('###'),
            'description' => $this->faker->optional()->sentence(),
            'category' => 'mail',
            'is_public' => false,
            'created_by' => User::factory(),
            'owner_organisation_id' => Organisation::factory(),
        ];
    }
}
