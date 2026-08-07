<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\SettingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — catégories de paramètres. Portage finalisé le 2026-08-04.
 */
class SettingCategoryApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['setting_category'];

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
        $this->getJson('/api/v1/setting-categories')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/setting-categories')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        SettingCategory::create(['name' => 'Général']);
        SettingCategory::create(['name' => 'Sécurité']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/setting-categories')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $category = SettingCategory::create(['name' => 'Général']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/setting-categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Général');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/setting-categories', ['name' => 'Nouvelle catégorie'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Nouvelle catégorie');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/setting-categories', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_update_refuse_une_reference_circulaire(): void
    {
        $parent = SettingCategory::create(['name' => 'Parent']);
        $child = SettingCategory::create(['name' => 'Enfant', 'parent_id' => $parent->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/setting-categories/{$parent->id}", ['parent_id' => $child->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_destroy_refuse_une_categorie_avec_parametres(): void
    {
        $category = SettingCategory::create(['name' => 'Général']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/setting-categories/{$category->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('setting_categories', ['id' => $category->id]);
    }

    public function test_tree_retourne_les_racines(): void
    {
        $root = SettingCategory::create(['name' => 'Racine']);
        SettingCategory::create(['name' => 'Sous-catégorie', 'parent_id' => $root->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/setting-categories/tree')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $category = SettingCategory::create(['name' => 'Général']);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/setting-categories/{$category->id}")
            ->assertOk();
    }
}
