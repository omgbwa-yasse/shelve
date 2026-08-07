<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Organisation>
 */
class OrganisationFactory extends Factory
{
    protected $model = \App\Models\Organisation::class;

    public function definition(): array
    {
        return [
            // `organisations.code` est un varchar(10) unique.
            'code' => Str::upper(Str::random(8)),
            'name' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'parent_id' => null,
        ];
    }

    public function child(int $parentId): static
    {
        return $this->state(fn () => ['parent_id' => $parentId]);
    }
}
