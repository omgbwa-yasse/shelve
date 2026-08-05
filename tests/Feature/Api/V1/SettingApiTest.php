<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Setting;
use App\Models\SettingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — paramètres applicatifs. Portage finalisé le 2026-08-04.
 */
class SettingApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['setting'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeCategory(): SettingCategory
    {
        return SettingCategory::create(['name' => 'Général']);
    }

    private function makeSetting(?SettingCategory $category = null): Setting
    {
        return Setting::create([
            'category_id' => ($category ?? $this->makeCategory())->id,
            'name' => 'setting_' . uniqid(),
            'type' => 'string',
            'default_value' => 'defaut',
            'description' => 'Paramètre de test',
            'is_system' => false,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/settings')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/settings')
            ->assertStatus(403);
    }

    public function test_index_retourne_les_parametres_globaux(): void
    {
        $this->makeSetting();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_ne_retourne_pas_les_parametres_d_une_autre_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $global = Setting::create([
            'category_id' => $this->makeCategory()->id,
            'name' => 'setting_global',
            'type' => 'string',
            'default_value' => 'defaut',
            'description' => 'Paramètre global',
        ]);
        Setting::create([
            'category_id' => $this->makeCategory()->id,
            'name' => 'setting_org_etrangere',
            'type' => 'string',
            'default_value' => 'defaut',
            'description' => 'Paramètre d\'une autre organisation',
            'organisation_id' => $orgEtrangere->id,
        ]);

        // Seul le paramètre global de l'agent est visible, pas celui de l'autre org.
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $global->id);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $setting = $this->makeSetting();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/settings/{$setting->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $setting->id)
            ->assertJsonStructure(['data' => ['effective_value']]);
    }

    public function test_store_cree_la_ressource(): void
    {
        $category = $this->makeCategory();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/settings', [
                'name' => 'parametre_1',
                'category_id' => $category->id,
                'type' => 'integer',
                'default_value' => '10',
                'description' => 'Un paramètre entier',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.type', 'integer');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/settings', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category_id', 'type', 'default_value', 'description']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $setting = $this->makeSetting();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/settings/{$setting->id}", ['description' => 'Modifié'])
            ->assertOk()
            ->assertJsonPath('data.description', 'Modifié');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $setting = $this->makeSetting();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/settings/{$setting->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('settings', ['id' => $setting->id]);
    }

    public function test_set_value_pose_une_valeur_personnalisee(): void
    {
        $setting = $this->makeSetting();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/settings/{$setting->id}/set-value", ['value' => 'ma valeur'])
            ->assertOk()
            ->assertJsonPath('data.value', 'ma valeur')
            ->assertJsonPath('data.effective_value', 'ma valeur');

        $this->assertSame('ma valeur', $setting->fresh()->value);
    }

    public function test_set_value_refuse_une_valeur_hors_contraintes(): void
    {
        $category = $this->makeCategory();
        $setting = Setting::create([
            'category_id' => $category->id,
            'name' => 'setting_int',
            'type' => 'integer',
            'default_value' => '0',
            'description' => 'Entier',
            'constraints' => ['min' => 0, 'max' => 100],
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/settings/{$setting->id}/set-value", ['value' => 250])
            ->assertStatus(422);
    }

    public function test_reset_value_efface_la_valeur_personnalisee(): void
    {
        $setting = $this->makeSetting();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/settings/{$setting->id}/set-value", ['value' => 'ma valeur'])
            ->assertOk();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/settings/{$setting->id}/reset-value")
            ->assertOk()
            ->assertJsonPath('data.value', null);

        $this->assertNull($setting->fresh()->value);
    }
}
