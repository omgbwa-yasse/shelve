<?php

namespace Tests\Feature\Api\V1;

use App\Models\Kpi;
use App\Models\Objective;
use App\Models\Organisation;
use App\Models\Project;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D17 — Projets / Tâches / OKR / KPI (voir evolution/PROJECT-OKR-KPI-PLAN.md).
 * Org-scopés (`organisation_id`) ; rattachement polymorphe (`attachable_type`/
 * `attachable_id`, alias court côté API) vers Workplace, Organisation ou User.
 */
class ProjectOkrKpiApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private User $user;
    private Organisation $organisation;
    private Workplace $workplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, ['project', 'objective', 'kpi']);
        $this->grantExtraPermissions($this->user, ['key_result_update', 'kpi_measurement_create']);

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
    }

    private function grantExtraPermissions(User $user, array $names): void
    {
        $ids = [];
        foreach ($names as $name) {
            $ids[] = \App\Models\Permission::firstOrCreate(
                ['name' => $name],
                ['category' => 'projects', 'description' => "{$name} (test)", 'guard_name' => 'web']
            )->id;
        }
        $user->permissions()->syncWithoutDetaching($ids);
    }

    private function makeProject(array $extra = []): Project
    {
        return Project::create([
            'code' => 'PRJ-' . uniqid(),
            'name' => 'Projet ' . substr(uniqid(), -6),
            'status' => 'active',
            'attachable_type' => Workplace::class,
            'attachable_id' => $this->workplace->id,
            'organisation_id' => $this->organisation->id,
            'created_by' => $this->user->id,
            ...$extra,
        ]);
    }

    // --- Projects ---------------------------------------------------------

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/projects')->assertStatus(401);
    }

    public function test_index_retourne_uniquement_les_projets_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeProject();
        $this->makeProject(['organisation_id' => $orgEtrangere->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_cree_un_projet_rattache_a_un_workplace(): void
    {
        $code = 'PRJ-NEW-' . uniqid();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/projects', [
                'code' => $code,
                'name' => 'Nouveau projet',
                'attachable_type' => 'workplace',
                'attachable_id' => $this->workplace->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Nouveau projet')
            ->assertJsonPath('data.attachable_type', 'workplace')
            ->assertJsonPath('data.attachable_id', $this->workplace->id)
            ->assertJsonPath('data.organisation_id', $this->organisation->id);

        $response->assertHeader('Location', "/api/v1/projects/{$response->json('data.id')}");

        $this->assertDatabaseHas('projects', [
            'code' => $code,
            'attachable_type' => Workplace::class,
            'attachable_id' => $this->workplace->id,
        ]);
    }

    public function test_store_accepte_un_rattachement_direct_a_une_personne(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/projects', [
                'code' => 'PRJ-PERSO-' . uniqid(),
                'name' => 'Projet personnel',
                'attachable_type' => 'user',
                'attachable_id' => $this->user->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.attachable_type', 'user')
            ->assertJsonPath('data.attachable_id', $this->user->id);
    }

    public function test_store_rejette_un_attachable_type_inconnu(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/projects', [
                'code' => 'PRJ-BAD',
                'name' => 'Projet invalide',
                'attachable_type' => 'planet',
                'attachable_id' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['attachable_type']);
    }

    public function test_update_modifie_le_projet(): void
    {
        $project = $this->makeProject();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/projects/{$project->id}", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_destroy_supprime_le_projet(): void
    {
        $project = $this->makeProject();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/projects/{$project->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    /** ⚠️ R03 : un projet d'une autre organisation répond 404, jamais 403. */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $project = $this->makeProject(['organisation_id' => $orgEtrangere->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$project->id}")
            ->assertStatus(404);
    }

    public function test_projet_ne_liste_pas_les_taches_d_un_autre_projet(): void
    {
        $project = $this->makeProject();
        $autreProjet = $this->makeProject();

        \App\Models\Task::create([
            'title' => 'Tâche du projet',
            'status' => 'pending',
            'priority' => 'normal',
            'taskable_type' => Project::class,
            'taskable_id' => $project->id,
            'organisation_id' => $this->organisation->id,
            'created_by' => $this->user->id,
        ]);
        \App\Models\Task::create([
            'title' => 'Tâche d\'un autre projet',
            'status' => 'pending',
            'priority' => 'normal',
            'taskable_type' => Project::class,
            'taskable_id' => $autreProjet->id,
            'organisation_id' => $this->organisation->id,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/projects/{$project->id}/tasks")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Tâche du projet');
    }

    // --- Objectives (OKR) + Key Results ------------------------------------

    public function test_store_objective_cree_les_resultats_cles_associes(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/objectives', [
                'title' => 'Améliorer le taux de numérisation',
                'attachable_type' => 'workplace',
                'attachable_id' => $this->workplace->id,
                'key_results' => [
                    ['title' => 'Numériser 500 dossiers', 'start_value' => 0, 'target_value' => 500, 'unit' => 'dossiers'],
                    ['title' => 'Réduire le délai de 30%', 'start_value' => 100, 'target_value' => 70, 'unit' => '%'],
                ],
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Améliorer le taux de numérisation')
            ->assertJsonCount(2, 'data.key_results');

        $objectiveId = $response->json('data.id');
        $this->assertEquals(2, \App\Models\KeyResult::where('objective_id', $objectiveId)->count());
        $this->assertDatabaseHas('key_results', ['objective_id' => $objectiveId, 'title' => 'Numériser 500 dossiers']);
    }

    public function test_key_result_progress_est_calculee_dynamiquement(): void
    {
        $objective = Objective::create([
            'title' => 'Objectif test',
            'attachable_type' => Workplace::class,
            'attachable_id' => $this->workplace->id,
            'organisation_id' => $this->organisation->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/objectives/{$objective->id}/key-results", [
                'title' => 'KR test',
                'start_value' => 0,
                'target_value' => 200,
                'current_value' => 50,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.progress', 0.25);

        $keyResultId = $response->json('data.id');

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/key-results/{$keyResultId}", ['current_value' => 200])
            ->assertOk()
            ->assertJsonPath('data.progress', 1);
    }

    // --- KPI + mesures ------------------------------------------------------

    public function test_store_kpi_puis_enregistre_des_mesures(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/kpis', [
                'code' => 'KPI-DELAI-' . uniqid(),
                'name' => 'Délai de traitement moyen',
                'unit' => 'jours',
                'direction' => 'lower_is_better',
                'frequency' => 'monthly',
                'attachable_type' => 'organisation',
                'attachable_id' => $this->organisation->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.attachable_type', 'organisation');

        $kpiId = $response->json('data.id');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/kpis/{$kpiId}/measurements", ['value' => 12.5, 'measured_at' => '2026-01-01'])
            ->assertStatus(201)
            ->assertJsonPath('data.value', 12.5);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/kpis/{$kpiId}/measurements", ['value' => 9.0, 'measured_at' => '2026-02-01'])
            ->assertStatus(201);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/kpis/{$kpiId}/measurements")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_kpi_rattache_a_une_organisation_etrangere_est_invisible(): void
    {
        $orgEtrangere = Organisation::factory()->create();

        $kpi = Kpi::create([
            'code' => 'KPI-ETRANGER-' . uniqid(),
            'name' => 'KPI étranger',
            'attachable_type' => Organisation::class,
            'attachable_id' => $orgEtrangere->id,
            'organisation_id' => $orgEtrangere->id,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/kpis/{$kpi->id}")
            ->assertStatus(404);
    }
}
