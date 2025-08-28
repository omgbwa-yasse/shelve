<?php

namespace Database\Factories;

use App\Models\MailContainer;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailContainer>
 */
class MailContainerFactory extends Factory
{
    protected $model = MailContainer::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('BOX#####')),
            'name' => $this->faker->optional()->word(),
            'property_id' => null, // if using newer schema; migration may still expect type_id. Keep null-safe.
            'created_by' => User::factory(),
            'creator_organisation_id' => Organisation::factory(),
        ];
    }
}
