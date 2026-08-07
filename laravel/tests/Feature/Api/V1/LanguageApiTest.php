<?php

namespace Tests\Feature\Api\V1;

use App\Models\Language;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — langues (référentiel). Portage finalisé le 2026-08-04.
 */
class LanguageApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['language'];

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
        $this->getJson('/api/v1/languages')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/languages')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        Language::create(['code' => 'fr', 'name' => 'Français']);
        Language::create(['code' => 'en', 'name' => 'Anglais']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/languages')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $language = Language::create(['code' => 'fr', 'name' => 'Français']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/languages/{$language->id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'fr');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/languages', ['code' => 'de', 'name' => 'Allemand'])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'de');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/languages', ['code' => 'troplong'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $language = Language::create(['code' => 'fr', 'name' => 'Français']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/languages/{$language->id}", ['name' => 'Français moderne'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Français moderne');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $language = Language::create(['code' => 'fr', 'name' => 'Français']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/languages/{$language->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
    }

    public function test_activate_accepte_une_locale_valide(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/languages/fr/activate')
            ->assertOk()
            ->assertJsonPath('data.locale', 'fr');
    }

    public function test_activate_refuse_une_locale_inconnue(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/languages/xx/activate')
            ->assertStatus(422);
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $language = Language::create(['code' => 'fr', 'name' => 'Français']);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/languages/{$language->id}")
            ->assertOk();
    }
}
