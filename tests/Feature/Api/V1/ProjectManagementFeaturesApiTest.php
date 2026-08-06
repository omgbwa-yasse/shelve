<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Project;
use App\Models\ProjectDeliverable;
use App\Models\ProjectMilestone;
use App\Models\ProjectResource;
use App\Models\Task;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * Extension MS-Project-parity de D17 : jalons, livrables, ressources,
 * rapports d'étape, dépendances entre tâches, alertes calculées.
 * Voir evolution/PROJECT-OKR-KPI-PLAN.md (extension).
 */
class ProjectManagementFeaturesApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private User $user;
    private Organisation $organisation;
    private Workplace $workplace;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, ['project', 'task']);
        $this->grantD01Permissions($this->user, ['project_milestone', 'project_deliverable', 'project_resource'], ['create', 'update', 'delete']);
        $this->grantD01Permissions($this->user, ['project_status_report'], ['create', 'delete']);
        $this->grantD01Permissions($this->user, ['task_dependency'], ['create', 'delete']);

        $this->workplace = Workplace::create([
            'code' => 'WP-' . uniqid(),
            'name' => 'Espace de test',
            'organisation_id' => $this->organisation->id,
            'owner_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);
        WorkplaceMember::create([
            'workplace_id' => $this->workplace->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $this->project = Project::create([
            'code' => 'PRJ-' . uniqid(),
            'name' => 'Projet ' . substr(uniqid(), -6),
            'status' => 'active',
            'attachable_type' => Workplace::class,
            'attachable_id' => $this->workplace->id,
            'organisation_id' => $this->organisation->id,
            'created_by' => $this->user->id,
        ]);
    }

    private function makeTask(array $extra = []): Task
    {
        return Task::create([
            'title' => 'Tâche ' . substr(uniqid(), -6),
            'status' => 'pending',
            'priority' => 'normal',
            'taskable_type' => Project::class,
            'taskable_id' => $this->project->id,
            'organisation_id' => $this->organisation->id,
            'created_by' => $this->user->id,
            ...$extra,
        ]);
    }

    // --- Jalons -------------------------------------------------------------

    public function test_store_puis_liste_un_jalon(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/projects/{$this->project->id}/milestones", [
                'name' => 'Phase 1 terminée',
                'due_date' => now()->addDays(10)->toDateString(),
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Phase 1 terminée')
            ->assertJsonPath('data.status', 'pending');

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$this->project->id}/milestones")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_reach_marque_le_jalon_atteint(): void
    {
        $milestone = $this->project->milestones()->create([
            'name' => 'Jalon test',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/project-milestones/{$milestone->id}/reach")
            ->assertOk()
            ->assertJsonPath('data.status', 'reached');

        $this->assertNotNull($milestone->fresh()->reached_at);
    }

    public function test_jalon_d_un_projet_d_une_autre_organisation_est_invisible(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $autreProjet = Project::create([
            'code' => 'PRJ-ETR-' . uniqid(),
            'name' => 'Projet étranger',
            'status' => 'active',
            'attachable_type' => Workplace::class,
            'attachable_id' => $this->workplace->id,
            'organisation_id' => $orgEtrangere->id,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$autreProjet->id}/milestones")
            ->assertStatus(404);
    }

    // --- Livrables ------------------------------------------------------------

    public function test_cycle_de_vie_livrable_soumis_puis_approuve(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/projects/{$this->project->id}/deliverables", [
                'name' => 'Rapport d\'audit',
                'due_date' => now()->addDays(5)->toDateString(),
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'draft');

        $id = $response->json('data.id');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/project-deliverables/{$id}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/project-deliverables/{$id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('project_deliverables', [
            'id' => $id,
            'status' => 'approved',
            'approved_by' => $this->user->id,
        ]);
    }

    public function test_rejette_un_livrable(): void
    {
        $deliverable = $this->project->deliverables()->create([
            'name' => 'Livrable à rejeter',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/project-deliverables/{$deliverable->id}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');
    }

    // --- Ressources -------------------------------------------------------

    public function test_store_ressources_de_chaque_type(): void
    {
        foreach (['human', 'financial', 'material', 'informational'] as $type) {
            $this->actingAs($this->user, 'sanctum')
                ->postJson("/api/v1/projects/{$this->project->id}/resources", [
                    'type' => $type,
                    'name' => "Ressource {$type}",
                    'planned_amount' => 1000,
                    'actual_amount' => 500,
                ])
                ->assertStatus(201)
                ->assertJsonPath('data.type', $type)
                ->assertJsonPath('data.is_over_budget', false);
        }

        $this->assertEquals(4, ProjectResource::where('project_id', $this->project->id)->count());
    }

    public function test_budget_actual_du_projet_agrege_les_ressources(): void
    {
        $this->project->update(['budget_planned' => 1000]);
        $this->project->resources()->create([
            'type' => 'financial',
            'name' => 'Prestataire',
            'planned_amount' => 800,
            'actual_amount' => 1200,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$this->project->id}")
            ->assertOk();

        $this->assertEquals(1200.0, (float) $response->json('data.budget_actual'));
    }

    // --- Rapports d'étape ---------------------------------------------------

    public function test_store_puis_liste_les_rapports_d_etape(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/projects/{$this->project->id}/status-reports", [
                'percent_complete' => 40,
                'summary' => 'Avancement conforme au planning.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.percent_complete', 40);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$this->project->id}/status-reports")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // --- Dépendances entre tâches -------------------------------------------

    public function test_cree_une_dependance_entre_deux_taches(): void
    {
        $predecesseur = $this->makeTask();
        $successeur = $this->makeTask();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/tasks/{$predecesseur->id}/dependencies", [
                'successor_id' => $successeur->id,
                'type' => 'finish_to_start',
                'lag_days' => 2,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.predecessor_id', $predecesseur->id)
            ->assertJsonPath('data.successor_id', $successeur->id)
            ->assertJsonPath('data.lag_days', 2);
    }

    public function test_rejette_une_dependance_d_une_tache_sur_elle_meme(): void
    {
        $tache = $this->makeTask();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/tasks/{$tache->id}/dependencies", [
                'successor_id' => $tache->id,
            ])
            ->assertStatus(422);
    }

    // --- Alertes calculées ---------------------------------------------------

    public function test_alertes_signalent_une_tache_en_retard(): void
    {
        $this->makeTask([
            'due_date' => now()->subDays(3),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$this->project->id}/alerts")
            ->assertOk();

        $types = collect($response->json('data'))->pluck('type');
        $this->assertTrue($types->contains('task_overdue'));
    }

    public function test_alertes_signalent_un_depassement_budgetaire(): void
    {
        $this->project->update(['budget_planned' => 100]);
        $this->project->resources()->create([
            'type' => 'financial',
            'name' => 'Dépense imprévue',
            'planned_amount' => 100,
            'actual_amount' => 500,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$this->project->id}/alerts")
            ->assertOk();

        $types = collect($response->json('data'))->pluck('type');
        $this->assertTrue($types->contains('budget_overrun'));
    }

    public function test_alertes_signalent_un_jalon_depasse(): void
    {
        $this->project->milestones()->create([
            'name' => 'Jalon en retard',
            'due_date' => now()->subDays(2),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$this->project->id}/alerts")
            ->assertOk();

        $types = collect($response->json('data'))->pluck('type');
        $this->assertTrue($types->contains('milestone_overdue'));
    }
}
