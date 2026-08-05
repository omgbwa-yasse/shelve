<?php

namespace Tests\Feature\Api\V1;

use App\Models\Activity;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — activités (référentiel). Portage finalisé le 2026-08-04.
 */
class ActivityApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['activity'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/activities')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/activities')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        Activity::create(['code' => 'ACT-1', 'name' => 'Activité A']);
        Activity::create(['code' => 'ACT-2', 'name' => 'Activité B']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/activities')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total'], 'links'])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_filtre_et_trie(): void
    {
        Activity::create(['code' => 'ACT-1', 'name' => 'Alpha']);
        Activity::create(['code' => 'ACT-2', 'name' => 'Beta']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/activities?filter[name]=Alpha&sort=-code')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Alpha');
    }

    public function test_index_refuse_un_filtre_inconnu(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/activities?filter[nom]=Alpha')
            ->assertStatus(400);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $activity = Activity::create(['code' => 'ACT-1', 'name' => 'Activité A']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/activities/{$activity->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $activity->id)
            ->assertJsonPath('data.code', 'ACT-1');
    }

    public function test_show_inconnu_renvoie_404(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/activities/999999')
            ->assertStatus(404);
    }

    public function test_store_cree_la_ressource(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/activities', ['code' => 'ACT-3', 'name' => 'Activité C'])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'ACT-3');

        // Location : pointe vers la ressource créée (id présent dans le corps).
        $id = $response->json('data.id');
        $response->assertHeader('Location', "/api/v1/activities/{$id}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/activities', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name']);

        // Unicité de `code` (reprise du contrôleur Blade).
        Activity::create(['code' => 'ACT-1', 'name' => 'Déjà pris']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/activities', ['code' => 'ACT-1', 'name' => 'Doublon'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $activity = Activity::create(['code' => 'ACT-1', 'name' => 'Avant']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/activities/{$activity->id}", ['name' => 'Après'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Après');

        $this->assertSame('Après', $activity->fresh()->name);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $activity = Activity::create(['code' => 'ACT-1', 'name' => 'À supprimer']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/activities/{$activity->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    }

    public function test_list_retourne_les_racines_sans_filtre(): void
    {
        $parent = Activity::create(['code' => 'ACT-1', 'name' => 'Mission']);
        Activity::create(['code' => 'ACT-2', 'name' => 'Sous-activité', 'parent_id' => $parent->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/activities/list')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_hierarchy_retourne_les_racines(): void
    {
        $parent = Activity::create(['code' => 'ACT-1', 'name' => 'Mission']);
        Activity::create(['code' => 'ACT-2', 'name' => 'Sous-activité', 'parent_id' => $parent->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/activities/hierarchy')
            ->assertOk()
            ->assertJsonCount(1, 'data.root_activities');
    }

    /**
     * Référentiel global (pas de `organisation_id`) : deux agents d'organisations
     * différentes accèdent aux mêmes données — aucune restriction ne doit s'appliquer
     * silencieusement (risque R03, sens « non-restriction »).
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $activity = Activity::create(['code' => 'ACT-1', 'name' => 'Partagée']);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/activities/{$activity->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $activity->id);
    }
}
