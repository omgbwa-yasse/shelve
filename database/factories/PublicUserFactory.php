<?php

namespace Database\Factories;

use App\Models\PublicUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublicUserFactory extends Factory
{
    protected $model = PublicUser::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->lastName,
            'first_name' => $this->faker->firstName,
            'phone1' => $this->faker->phoneNumber,
            'phone2' => $this->faker->optional(0.3)->phoneNumber,
            'address' => $this->faker->address,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => $this->faker->optional(0.8)->dateTime(),
            'password' => Hash::make('password'),
            'is_approved' => $this->faker->boolean(80),
            'remember_token' => Str::random(10),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
            'email_verified_at' => null,
        ]);
    }
}
