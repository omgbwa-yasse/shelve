<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\ReferenceList;
use App\Models\ReferenceValue;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — listes de référence et leurs valeurs. Portage finalisé le 2026-08-04.
 */
class ReferenceListApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['reference_list'];

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
        $this->getJson('/api/v1/reference-lists')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/reference-lists')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        ReferenceList::create(['name' => 'Civilités', 'code' => 'CIV', 'created_by' => $this->user->id]);
        ReferenceList::create(['name' => 'Formats', 'code' => 'FMT', 'created_by' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/reference-lists')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure(['data' => [['values_count']]]);
    }

    public function test_show_retourne_la_ressource_et_ses_valeurs(): void
    {
        $list = ReferenceList::create(['name' => 'Civilités', 'code' => 'CIV', 'created_by' => $this->user->id]);
        // `reference_values.created_by` est NOT NULL sans valeur par défaut.
        ReferenceValue::create(['list_id' => $list->id, 'value' => 'M.', 'code' => 'M', 'created_by' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/reference-lists/{$list->id}")
            ->assertOk()
            ->assertJsonPath('data.values_count', 1)
            ->assertJsonCount(1, 'data.values');
    }

    public function test_store_cree_la_ressource_et_le_creator(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/reference-lists', ['name' => 'Civilités', 'code' => 'CIV'])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'CIV')
            ->assertJsonPath('data.created_by', $this->user->id);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/reference-lists', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'code']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $list = ReferenceList::create(['name' => 'Civilités', 'code' => 'CIV', 'created_by' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/reference-lists/{$list->id}", ['name' => 'Titres de civilité'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Titres de civilité')
            ->assertJsonPath('data.updated_by', $this->user->id);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $list = ReferenceList::create(['name' => 'Civilités', 'code' => 'CIV', 'created_by' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/reference-lists/{$list->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('reference_lists', ['id' => $list->id]);
    }

    public function test_add_value_ajoute_une_valeur(): void
    {
        $list = ReferenceList::create(['name' => 'Civilités', 'code' => 'CIV', 'created_by' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/reference-lists/{$list->id}/values", ['value' => 'M.', 'code' => 'M'])
            ->assertStatus(201)
            ->assertJsonPath('data.list_id', $list->id)
            ->assertJsonPath('data.code', 'M');
    }

    public function test_add_value_refuse_un_code_duplique(): void
    {
        $list = ReferenceList::create(['name' => 'Civilités', 'code' => 'CIV', 'created_by' => $this->user->id]);
        ReferenceValue::create(['list_id' => $list->id, 'value' => 'M.', 'code' => 'M', 'created_by' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/reference-lists/{$list->id}/values", ['value' => 'Mme', 'code' => 'M'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_update_value_modifie_la_valeur(): void
    {
        $list = ReferenceList::create(['name' => 'Civilités', 'code' => 'CIV', 'created_by' => $this->user->id]);
        $value = ReferenceValue::create(['list_id' => $list->id, 'value' => 'M.', 'code' => 'M', 'created_by' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/reference-lists/{$list->id}/values/{$value->id}", ['value' => 'Monsieur'])
            ->assertOk()
            ->assertJsonPath('data.value', 'Monsieur');
    }

    public function test_delete_value_supprime_la_valeur(): void
    {
        $list = ReferenceList::create(['name' => 'Civilités', 'code' => 'CIV', 'created_by' => $this->user->id]);
        $value = ReferenceValue::create(['list_id' => $list->id, 'value' => 'M.', 'code' => 'M', 'created_by' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/reference-lists/{$list->id}/values/{$value->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('reference_values', ['id' => $value->id]);
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $list = ReferenceList::create(['name' => 'Civilités', 'code' => 'CIV', 'created_by' => $this->user->id]);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/reference-lists/{$list->id}")
            ->assertOk();
    }
}

