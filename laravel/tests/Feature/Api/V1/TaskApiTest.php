<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D12 — tâches. Vérification faite : le modèle n'utilise pas BelongsToOrganisation
 * et le Blade ne filtre pas par organisation → référentiel global. Portage
 * finalisé le 2026-08-04.
 */
class TaskApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['task'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeTask(array $extra = []): Task
    {
        return Task::create([
            'organisation_id' => $this->user->current_organisation_id,
            'title' => 'Tâche de test',
            'status' => 'pending',
            'priority' => 'normal',
            'created_by' => $this->user->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/tasks')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/tasks')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makeTask();
        $this->makeTask();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_peut_filtrer_par_statut(): void
    {
        $this->makeTask();
        $this->makeTask(['status' => 'completed']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/tasks?filter[status]=completed')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_show_retourne_la_ressource(): void
    {
        $task = $this->makeTask();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Tâche de test');
    }

    public function test_store_cree_la_ressource_avec_le_creator_authentifie(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/tasks', [
                'title' => 'Nouvelle tâche',
                'status' => 'pending',
                'priority' => 'high',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Nouvelle tâche')
            ->assertJsonPath('data.created_by', $this->user->id);

        $response->assertHeader('Location', "/api/v1/tasks/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/tasks', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status', 'priority']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $task = $this->makeTask();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/tasks/{$task->id}", ['status' => 'in_progress'])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.updated_by', $this->user->id);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $task = $this->makeTask();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/tasks/{$task->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
