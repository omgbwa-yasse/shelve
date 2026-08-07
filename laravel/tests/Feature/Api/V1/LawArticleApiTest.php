<?php

namespace Tests\Feature\Api\V1;

use App\Models\Law;
use App\Models\LawArticle;
use App\Models\LawType;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — articles de loi (référentiel). Portage finalisé le 2026-08-04.
 *
 * `LawArticlePolicy` utilise les permissions `law_*` (pas `law_article_*`) : un
 * article n'a pas de sens hors de sa loi.
 */
class LawArticleApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['law'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeLaw(): Law
    {
        return Law::create([
            'code' => 'L1',
            'name' => 'Loi 1',
            'publish_date' => '2020-01-01',
            'law_type_id' => LawType::create(['name' => 'Loi'])->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/law-articles')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/law-articles')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $law = $this->makeLaw();
        LawArticle::create(['code' => 'A1', 'name' => 'Article 1', 'law_id' => $law->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/law-articles')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $article = LawArticle::create(['code' => 'A1', 'name' => 'Article 1', 'law_id' => $this->makeLaw()->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/law-articles/{$article->id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'A1');
    }

    public function test_store_cree_la_ressource(): void
    {
        $law = $this->makeLaw();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/law-articles', ['code' => 'A2', 'name' => 'Article 2', 'law_id' => $law->id])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'A2');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/law-articles', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'law_id']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $article = LawArticle::create(['code' => 'A1', 'name' => 'Article 1', 'law_id' => $this->makeLaw()->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/law-articles/{$article->id}", ['name' => 'Article 1 bis'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Article 1 bis');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $article = LawArticle::create(['code' => 'A1', 'name' => 'Article 1', 'law_id' => $this->makeLaw()->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/law-articles/{$article->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('law_articles', ['id' => $article->id]);
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $article = LawArticle::create(['code' => 'A1', 'name' => 'Article 1', 'law_id' => $this->makeLaw()->id]);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/law-articles/{$article->id}")
            ->assertOk();
    }
}
