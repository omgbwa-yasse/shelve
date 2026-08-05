<?php

namespace Tests\Feature\Api\V1;

use App\Models\PublicEvent;
use App\Models\PublicEventRegistration;
use App\Models\PublicUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * D15 — événements du portail public. Lecture publique ; inscription réservée
 * aux usagers connectés (guard public, token Sanctum).
 */
class PublicEventApiTest extends TestCase
{
    use DatabaseTransactions;

    private function makeEvent(array $overrides = []): PublicEvent
    {
        return PublicEvent::create(array_merge([
            'name' => 'Conférence',
            'description' => 'Description.',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(10)->addHours(2),
            'location' => 'Salle A',
            'is_online' => false,
        ], $overrides));
    }

    private function makePublicUser(string $email = 'usager@test.local'): PublicUser
    {
        return PublicUser::create([
            'name' => 'Dupont',
            'first_name' => 'Jean',
            'phone1' => '0123456789',
            'phone2' => '',
            'address' => '1 rue du Test',
            'email' => $email,
            'password' => Hash::make('secret-123'),
            'is_approved' => true,
        ]);
    }

    public function test_index_est_public(): void
    {
        $this->getJson('/api/public/events')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total'], 'links']);
    }

    public function test_index_filtre_par_defaut_sur_les_evenements_a_venir(): void
    {
        $this->makeEvent(['name' => 'À venir']);
        $this->makeEvent(['name' => 'Passé', 'start_date' => now()->subDays(5), 'end_date' => now()->subDays(4)]);

        $this->getJson('/api/public/events')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'À venir');
    }

    public function test_show_retourne_l_evenement_avec_le_compteur(): void
    {
        $event = $this->makeEvent();

        $this->getJson("/api/public/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $event->id)
            ->assertJsonStructure(['data' => ['registration_count']]);
    }

    public function test_inscription_necessite_un_token(): void
    {
        $event = $this->makeEvent();

        $this->postJson("/api/public/events/{$event->id}/registrations", ['notes' => 'Vélo'])
            ->assertStatus(401);
    }

    public function test_inscription_cree_la_registration(): void
    {
        $event = $this->makeEvent();
        $user = $this->makePublicUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/public/events/{$event->id}/registrations", ['notes' => 'Rang 5'])
            ->assertStatus(201)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.event_id', $event->id)
            ->assertJsonPath('data.status', 'registered');

        $id = $response->json('data.id');
        $this->assertDatabaseHas('public_event_registrations', ['id' => $id, 'user_id' => $user->id]);
    }

    public function test_inscription_doublon_renvoie_409(): void
    {
        $event = $this->makeEvent();
        $user = $this->makePublicUser();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/public/events/{$event->id}/registrations")
            ->assertStatus(201);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/public/events/{$event->id}/registrations")
            ->assertStatus(409);
    }

    public function test_consultation_de_la_registration_de_l_usager(): void
    {
        $event = $this->makeEvent();
        $user = $this->makePublicUser();

        $registration = PublicEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
            'registered_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/public/events/{$event->id}/registrations")
            ->assertOk()
            ->assertJsonPath('data.id', $registration->id);
    }

    public function test_annulation_supprime_la_registration(): void
    {
        $event = $this->makeEvent();
        $user = $this->makePublicUser();

        PublicEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
            'registered_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/public/events/{$event->id}/registrations")
            ->assertNoContent();

        $this->assertSoftDeleted('public_event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_consultation_sans_inscription_renvoie_404(): void
    {
        $event = $this->makeEvent();
        $user = $this->makePublicUser();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/public/events/{$event->id}/registrations")
            ->assertStatus(404);
    }
}
