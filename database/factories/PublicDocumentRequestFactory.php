<?php

namespace Database\Factories;

use App\Models\PublicDocumentRequest;
use App\Models\PublicUser;
use App\Models\PublicRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicDocumentRequestFactory extends Factory
{
    protected $model = PublicDocumentRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => PublicUser::factory(),
            'record_id' => PublicRecord::factory(),
            'request_type' => $this->faker->randomElement(['digital', 'physical']),
            'reason' => $this->faker->optional(0.7)->paragraph,
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed']),
            'admin_notes' => $this->faker->optional(0.3)->paragraph,
            'processed_at' => $this->faker->optional(0.5)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_at' => null,
            'admin_notes' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'processed_at' => now(),
        ]);
    }

    public function digital(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => 'digital',
        ]);
    }

    public function physical(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => 'physical',
        ]);
    }
}
