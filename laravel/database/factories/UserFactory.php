<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            // `users.birthday` est NOT NULL sans valeur par défaut : l'omettre
            // provoque une erreur SQL 1364.
            'birthday' => $this->faker->date(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => \Illuminate\Support\Str::random(10),
            'current_organisation_id' => Organisation::factory(),
        ];
    }

    /**
     * Rattache l'agent à une organisation existante plutôt que d'en créer une.
     */
    public function forOrganisation(Organisation $organisation): static
    {
        return $this->state(fn () => [
            'current_organisation_id' => $organisation->id,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
