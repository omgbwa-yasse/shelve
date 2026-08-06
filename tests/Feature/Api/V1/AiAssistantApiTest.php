<?php

namespace Tests\Feature\Api\V1;

use App\Models\AiConversation;
use App\Models\AiRoutine;
use App\Models\Organisation;
use App\Models\Prompt;
use App\Models\User;
use App\Services\AI\AiAssistantChatService;
use App\Services\AI\AiRoutineExecutionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * Assistant IA du panneau latéral (onglets Chat/Routine/Historique), voir
 * demande utilisateur du 2026-08-05. Les appels LLM réels (`AiBridge`) sont
 * remplacés par un double en conteneur — ces tests vérifient le contrat
 * HTTP/persistance, pas la qualité des réponses d'un modèle.
 */
class AiAssistantApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, ['ai_routine']);

        $this->app->bind(AiAssistantChatService::class, function () {
            $fake = \Mockery::mock(AiAssistantChatService::class);
            $fake->shouldReceive('reply')->andReturn(['success' => true, 'reply' => 'Réponse simulée de l\'assistant.']);

            return $fake;
        });
    }

    // --- Conversations (Chat + Historique) ----------------------------------

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/ai/conversations')->assertStatus(401);
    }

    public function test_store_demarre_une_conversation_avec_une_reponse_assistant(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai/conversations', [
                'message' => 'Combien de dossiers sont en attente de versement ?',
                'context' => ['path' => '/records', 'search' => ''],
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Combien de dossiers sont en attente de versement ?')
            ->assertJsonCount(2, 'data.messages');

        $this->assertEquals('user', $response->json('data.messages.0.role'));
        $this->assertEquals('assistant', $response->json('data.messages.1.role'));
        $this->assertEquals('Réponse simulée de l\'assistant.', $response->json('data.messages.1.content'));
    }

    public function test_store_utilise_le_mode_manuel_par_defaut(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai/conversations', ['message' => 'Question sans mode précisé'])
            ->assertStatus(201);

        $this->assertEquals('manuel', $response->json('data.mode'));
    }

    public function test_store_accepte_un_mode_explicite(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai/conversations', ['message' => 'Planifie ceci', 'mode' => 'plan'])
            ->assertStatus(201);

        $this->assertEquals('plan', $response->json('data.mode'));
    }

    public function test_store_rejette_un_mode_inconnu(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai/conversations', ['message' => 'Test', 'mode' => 'god'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['mode']);
    }

    public function test_send_message_peut_changer_le_mode_en_cours_de_fil(): void
    {
        $conversationId = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai/conversations', ['message' => 'Début', 'mode' => 'manuel'])
            ->json('data.id');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/ai/conversations/{$conversationId}/messages", ['message' => 'Passe en autonome', 'mode' => 'autonome'])
            ->assertOk();

        $this->assertEquals('autonome', $response->json('data.mode'));
        $this->assertDatabaseHas('ai_conversations', ['id' => $conversationId, 'mode' => 'autonome']);
    }

    public function test_send_message_poursuit_la_conversation(): void
    {
        $conversationId = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai/conversations', ['message' => 'Première question'])
            ->json('data.id');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/ai/conversations/{$conversationId}/messages", ['message' => 'Deuxième question'])
            ->assertOk()
            ->assertJsonCount(4, 'data.messages');
    }

    public function test_index_liste_uniquement_les_conversations_de_l_agent(): void
    {
        $autreUser = User::factory()->forOrganisation($this->organisation)->create();
        AiConversation::create(['organisation_id' => $this->organisation->id, 'user_id' => $autreUser->id, 'title' => 'Autre agent']);
        AiConversation::create(['organisation_id' => $this->organisation->id, 'user_id' => $this->user->id, 'title' => 'Mon fil']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/ai/conversations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Mon fil');
    }

    public function test_conversation_d_un_autre_agent_est_invisible(): void
    {
        $autreUser = User::factory()->forOrganisation($this->organisation)->create();
        $conversation = AiConversation::create(['organisation_id' => $this->organisation->id, 'user_id' => $autreUser->id, 'title' => 'Privé']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/ai/conversations/{$conversation->id}")
            ->assertStatus(404);
    }

    /**
     * ⚠️ L'historique de chat ne doit jamais être supprimé (exigence
     * utilisateur du 2026-08-05) : "destroy" archive (masque de l'onglet
     * Historique) mais la ligne reste en base, jamais purgée.
     */
    public function test_destroy_archive_la_conversation_sans_la_supprimer(): void
    {
        $conversationId = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai/conversations', ['message' => 'À archiver'])
            ->json('data.id');

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/ai/conversations/{$conversationId}")
            ->assertNoContent();

        $this->assertDatabaseHas('ai_conversations', ['id' => $conversationId]);
        $this->assertNotNull(AiConversation::find($conversationId)->archived_at);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/ai/conversations')
            ->assertOk()
            ->assertJsonMissing(['id' => (int) $conversationId]);
    }

    // --- Routines -------------------------------------------------------------

    public function test_store_routine_hebdomadaire_calcule_next_run_at(): void
    {
        $prompt = Prompt::create([
            'title' => 'Synthèse hebdo',
            'content' => 'Résume les dossiers créés cette semaine.',
            'is_system' => false,
            'organisation_id' => $this->organisation->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai/routines', [
                'name' => 'Synthèse hebdomadaire',
                'prompt_id' => $prompt->id,
                'schedule_type' => 'weekly',
                'run_time' => '08:00',
                'day_of_week' => 1,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Synthèse hebdomadaire')
            ->assertJsonPath('data.schedule_type', 'weekly');

        $this->assertNotNull($response->json('data.next_run_at'));
    }

    public function test_store_routine_sans_prompt_ni_skill_est_rejetee(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai/routines', ['name' => 'Routine invalide', 'schedule_type' => 'once'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['prompt_id']);
    }

    public function test_run_execute_immediatement_et_journalise_le_resultat(): void
    {
        $this->app->bind(AiRoutineExecutionService::class, function () {
            $fake = \Mockery::mock(AiRoutineExecutionService::class);
            $fake->shouldReceive('execute')->andReturn(['status' => 'success', 'output' => 'Synthèse générée.']);

            return $fake;
        });

        $prompt = Prompt::create([
            'title' => 'Prompt test',
            'content' => 'Contenu',
            'is_system' => false,
            'organisation_id' => $this->organisation->id,
            'user_id' => $this->user->id,
        ]);

        $routine = AiRoutine::create([
            'organisation_id' => $this->organisation->id,
            'created_by' => $this->user->id,
            'name' => 'Routine ponctuelle',
            'prompt_id' => $prompt->id,
            'schedule_type' => 'once',
            'next_run_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/ai/routines/{$routine->id}/run")
            ->assertOk()
            ->assertJsonPath('data.last_status', 'success')
            ->assertJsonPath('data.last_output', 'Synthèse générée.');

        $this->assertDatabaseHas('ai_routines', ['id' => $routine->id, 'last_status' => 'success']);
    }

    public function test_isolation_entre_organisations_pour_les_routines(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $routine = AiRoutine::create([
            'organisation_id' => $orgEtrangere->id,
            'name' => 'Routine étrangère',
            'schedule_type' => 'once',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/ai/routines/{$routine->id}")
            ->assertStatus(404);
    }

    public function test_commande_ai_routines_run_due_execute_les_routines_arrivees_a_echeance(): void
    {
        $this->app->bind(AiRoutineExecutionService::class, function () {
            $fake = \Mockery::mock(AiRoutineExecutionService::class);
            $fake->shouldReceive('execute')->andReturn(['status' => 'success', 'output' => 'OK planifié.']);

            return $fake;
        });

        $prompt = Prompt::create([
            'title' => 'Prompt planifié',
            'content' => 'Contenu',
            'is_system' => false,
            'organisation_id' => $this->organisation->id,
            'user_id' => $this->user->id,
        ]);

        $due = AiRoutine::create([
            'organisation_id' => $this->organisation->id,
            'name' => 'Due',
            'prompt_id' => $prompt->id,
            'schedule_type' => 'hourly',
            'is_enabled' => true,
            'next_run_at' => now()->subMinute(),
        ]);

        $notYetDue = AiRoutine::create([
            'organisation_id' => $this->organisation->id,
            'name' => 'Pas encore due',
            'prompt_id' => $prompt->id,
            'schedule_type' => 'hourly',
            'is_enabled' => true,
            'next_run_at' => now()->addHour(),
        ]);

        $this->artisan('ai:routines:run-due')->assertSuccessful();

        $this->assertEquals('success', $due->fresh()->last_status);
        $this->assertNull($notYetDue->fresh()->last_status);
    }
}
