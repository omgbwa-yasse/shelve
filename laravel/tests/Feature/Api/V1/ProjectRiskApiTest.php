<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * Registre des risques projet (D17, extension MS-Project-parity) — même
 * patron que ProjectManagementFeaturesApiTest (jalons/livrables/ressources).
 */
class ProjectRiskApiTest extends TestCase
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
        $this->grantD01Permissions($this->user, ['project']);
        $this->grantD01Permissions($this->user, ['project_risk'], ['create', 'update', 'delete']);

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

    public function test_store_puis_liste_un_risque(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/projects/{$this->project->id}/risks", [
                'title' => 'Retard fournisseur critique',
                'category' => 'external',
                'probability' => 'high',
                'impact' => 'high',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Retard fournisseur critique')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.score', 9)
            ->assertJsonPath('data.criticality', 'high');

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$this->project->id}/risks")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_criticite_faible_pour_probabilite_et_impact_bas(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/projects/{$this->project->id}/risks", [
                'title' => 'Risque mineur',
                'probability' => 'low',
                'impact' => 'low',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.score', 1)
            ->assertJsonPath('data.criticality', 'low');
    }

    public function test_mitigate_puis_close(): void
    {
        $risk = $this->project->risks()->create([
            'title' => 'Risque à traiter',
            'probability' => 'medium',
            'impact' => 'medium',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/project-risks/{$risk->id}/mitigate", ['mitigation_plan' => 'Plan B activé'])
            ->assertOk()
            ->assertJsonPath('data.status', 'mitigated')
            ->assertJsonPath('data.mitigation_plan', 'Plan B activé');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/project-risks/{$risk->id}/close")
            ->assertOk()
            ->assertJsonPath('data.status', 'closed');

        $this->assertNotNull($risk->fresh()->resolved_at);
    }

    public function test_occur_marque_le_risque_materialise(): void
    {
        $risk = $this->project->risks()->create([
            'title' => 'Risque qui se produit',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/project-risks/{$risk->id}/occur")
            ->assertOk()
            ->assertJsonPath('data.status', 'occurred');
    }

    public function test_destroy_supprime_le_risque(): void
    {
        $risk = $this->project->risks()->create([
            'title' => 'À supprimer',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/project-risks/{$risk->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('project_risks', ['id' => $risk->id]);
    }

    public function test_risque_d_un_projet_d_une_autre_organisation_est_invisible(): void
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
            ->getJson("/api/v1/projects/{$autreProjet->id}/risks")
            ->assertStatus(404);
    }

    public function test_risque_peut_etre_rattache_a_une_tache(): void
    {
        $task = Task::create([
            'title' => 'Tâche à risque',
            'status' => 'pending',
            'priority' => 'normal',
            'taskable_type' => Project::class,
            'taskable_id' => $this->project->id,
            'organisation_id' => $this->organisation->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/projects/{$this->project->id}/risks", [
                'title' => 'Risque lié à la tâche',
                'task_id' => $task->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.task_id', $task->id)
            ->assertJsonPath('data.task_title', 'Tâche à risque');
    }

    public function test_risque_critique_ouvert_apparait_dans_les_alertes(): void
    {
        $this->project->risks()->create([
            'title' => 'Risque critique',
            'probability' => 'high',
            'impact' => 'high',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$this->project->id}/alerts")
            ->assertOk()
            ->assertJsonFragment(['type' => 'risk_critical']);
    }
}
