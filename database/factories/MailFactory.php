<?php

namespace Database\Factories;

use App\Models\Mail;
use App\Models\MailTypology;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mail>
 */
class MailFactory extends Factory
{
    protected $model = Mail::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('ML########')),
            'name' => $this->faker->sentence(3),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'description' => $this->faker->optional()->paragraph(),
            'document_type' => $this->faker->randomElement(['original','duplicate','copy']),
            'status' => 'draft',
            'priority_id' => null,
            'typology_id' => function () {
                // If typology table exists and has entries, use first or create a dummy via factory if available
                return MailTypology::query()->value('id') ?? 1;
            },
            'action_id' => null,
            'sender_user_id' => null,
            'sender_organisation_id' => null,
            'recipient_user_id' => null,
            'recipient_organisation_id' => null,
            'mail_type' => $this->faker->randomElement(['internal','incoming','outgoing']),
            'is_archived' => false,
        ];
    }
}
