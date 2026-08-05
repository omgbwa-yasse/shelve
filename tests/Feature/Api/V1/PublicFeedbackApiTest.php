<?php

namespace Tests\Feature\Api\V1;

use App\Models\PublicFeedback;
use App\Models\PublicUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * D15 — feedback du portail public. Réservé aux usagers connectés (guard
 * public, token Sanctum) : `user_id` vient du token (colonne NOT NULL).
 */
class PublicFeedbackApiTest extends TestCase
{
    use DatabaseTransactions;

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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'subject' => 'Site inaccessible',
            'content' => 'La page d\'accueil ne charge pas.',
            'type' => 'bug',
            'priority' => 'high',
            'rating' => 3,
        ], $overrides);
    }

    public function test_store_necessite_un_token(): void
    {
        $this->postJson('/api/public/feedbacks', $this->validPayload())
            ->assertStatus(401);
    }

    public function test_store_cree_le_feedback_pour_l_usager_connecte(): void
    {
        $user = $this->makePublicUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/public/feedbacks', $this->validPayload())
            ->assertStatus(201)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.subject', 'Site inaccessible')
            ->assertJsonPath('data.type', 'bug')
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.rating', 3);

        $this->assertDatabaseHas('public_feedbacks', [
            'id' => $response->json('data.id'),
            'user_id' => $user->id,
        ]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $user = $this->makePublicUser();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/public/feedbacks', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subject', 'content', 'type', 'priority']);

        // `type` est une enum en base : une valeur hors liste est rejetée.
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/public/feedbacks', $this->validPayload(['type' => 'urgent']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('type');
    }

    public function test_index_necessite_un_token(): void
    {
        $this->getJson('/api/public/feedbacks')->assertStatus(401);
    }

    public function test_index_ne_retourne_que_les_feedbacks_de_l_usager(): void
    {
        $user = $this->makePublicUser('jean@test.local');
        $autre = $this->makePublicUser('marie@test.local');

        PublicFeedback::create([
            'user_id' => $user->id,
            'subject' => 'Mon feedback',
            'content' => 'Contenu.',
            'type' => 'feature',
            'priority' => 'low',
            'status' => 'new',
        ]);

        PublicFeedback::create([
            'user_id' => $autre->id,
            'subject' => 'Feedback de Marie',
            'content' => 'Contenu.',
            'type' => 'bug',
            'priority' => 'high',
            'status' => 'new',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/public/feedbacks')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.subject', 'Mon feedback');
    }
}
