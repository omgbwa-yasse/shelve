<?php

namespace Database\Factories;

use App\Models\PublicEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicEventFactory extends Factory
{
    protected $model = PublicEvent::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+6 months');
        $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s') . ' +4 hours');

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraphs(2, true),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'location' => $this->faker->optional(0.8)->address,
            'is_online' => $this->faker->boolean(30),
            'online_link' => $this->faker->optional(0.3)->url,
        ];
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_online' => true,
            'online_link' => $this->faker->url,
            'location' => null,
        ]);
    }

    public function physical(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_online' => false,
            'online_link' => null,
            'location' => $this->faker->address,
        ]);
    }

    public function upcoming(): static
    {
        $startDate = $this->faker->dateTimeBetween('+1 day', '+3 months');
        $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s') . ' +4 hours');

        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
