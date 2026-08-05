<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\User;
use App\Models\WorkplaceConversation;
use App\Models\WorkplaceConversationParticipant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D12 — conversations / chats (WorkplaceConversation, fusion de ChatController et
 * WorkplaceMessageController). L'accès est limité aux participants (403), le
 * créateur seul peut supprimer. Portage finalisé le 2026-08-04.
 */
class WorkplaceConversationApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['workplace_conversation'];

    private User $user;
    private User $other;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->other = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeConversation(User $creator, array $userIds, array $extra = []): WorkplaceConversation
    {
        $conversation = WorkplaceConversation::create([
            'type' => 'private',
            'created_by' => $creator->id,
            ...$extra,
        ]);

        foreach (array_unique(array_merge([$creator->id], $userIds)) as $userId) {
            WorkplaceConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'role' => $userId === $creator->id ? 'owner' : 'member',
            ]);
        }

        return $conversation;
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/workplace-conversations')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/workplace-conversations')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_conversations_de_l_utilisateur(): void
    {
        $this->makeConversation($this->user, [$this->other->id]);

        $etranger = User::factory()->forOrganisation($this->organisation)->create();
        $this->makeConversation($etranger, [$this->other->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/workplace-conversations')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_store_cree_une_conversation_privee(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workplace-conversations', [
                'type' => 'private',
                'participant_ids' => [$this->other->id],
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.created_by', $this->user->id);

        $conversationId = $response->json('data.id');

        $this->assertDatabaseHas('workplace_conversation_participants', [
            'conversation_id' => $conversationId,
            'user_id' => $this->other->id,
        ]);
    }

    public function test_store_reutilise_une_conversation_privee_existante(): void
    {
        $existing = $this->makeConversation($this->user, [$this->other->id]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workplace-conversations', [
                'type' => 'private',
                'participant_ids' => [$this->other->id],
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $existing->id);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workplace-conversations', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'participant_ids']);
    }

    public function test_show_d_un_non_participant_est_refuse(): void
    {
        $etranger = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($etranger, self::PERMISSIONS);
        $conversation = $this->makeConversation($this->user, [$this->other->id]);

        $this->actingAs($etranger, 'sanctum')
            ->getJson("/api/v1/workplace-conversations/{$conversation->id}")
            ->assertStatus(403);
    }

    public function test_store_message_ajoute_le_message(): void
    {
        $conversation = $this->makeConversation($this->user, [$this->other->id]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workplace-conversations/{$conversation->id}/messages", ['content' => 'Bonjour'])
            ->assertStatus(201)
            ->assertJsonPath('data.content', 'Bonjour')
            ->assertJsonPath('data.user_id', $this->user->id);
    }

    public function test_destroy_seul_le_createur_peut_supprimer(): void
    {
        $this->grantD01Permissions($this->other, self::PERMISSIONS);
        $conversation = $this->makeConversation($this->user, [$this->other->id]);

        $this->actingAs($this->other, 'sanctum')
            ->deleteJson("/api/v1/workplace-conversations/{$conversation->id}")
            ->assertStatus(403);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workplace-conversations/{$conversation->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('workplace_conversations', ['id' => $conversation->id]);
    }
}
