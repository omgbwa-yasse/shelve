<?php

namespace Tests\Feature\Api\V1;

use App\Models\LawArticle;
use App\Models\Law;
use App\Models\LawType;
use App\Models\Organisation;
use App\Models\Retention;
use App\Models\RetentionLawArticle;
use App\Models\Sort;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D07 — exigence réglementaire (pivot rétention ↔ article de loi). La Policy
 * réutilise les permissions `retention_*`. Portage finalisé le 2026-08-04.
 */
class RetentionLawArticleApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['retention'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeRetention(): Retention
    {
        $sort = Sort::create(['code' => 'C', 'name' => 'Conservation']);

        return Retention::create([
            'code' => 'R' . substr(uniqid(), -6),
            'name' => 'Durée',
            'duration' => 30,
            'sort_id' => $sort->id,
        ]);
    }

    private function makeLawArticle(): LawArticle
    {
        $lawType = LawType::create(['name' => 'Loi']);
        $law = Law::create([
            'code' => 'L-' . substr(uniqid(), -6),
            'name' => 'Loi ' . substr(uniqid(), -6),
            'law_type_id' => $lawType->id,
            'publish_date' => now()->toDateString(),
        ]);

        return LawArticle::create(['code' => 'ART-' . substr(uniqid(), -6), 'name' => 'Article ' . substr(uniqid(), -6), 'law_id' => $law->id]);
    }

    private function makePivot(): RetentionLawArticle
    {
        $retention = $this->makeRetention();
        $article = $this->makeLawArticle();

        return RetentionLawArticle::create([
            'retention_id' => $retention->id,
            'law_article_id' => $article->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/retention-law-articles')->assertStatus(401);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makePivot();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/retention-law-articles')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_cree_la_liaison(): void
    {
        $retention = $this->makeRetention();
        $article = $this->makeLawArticle();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/retention-law-articles', [
                'retention_id' => $retention->id,
                'law_article_id' => $article->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.law_article_id', $article->id);

        $this->assertDatabaseHas('retention_law_articles', [
            'retention_id' => $retention->id,
            'law_article_id' => $article->id,
        ]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/retention-law-articles', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['retention_id', 'law_article_id']);
    }

    public function test_destroy_retire_la_liaison(): void
    {
        $pivot = $this->makePivot();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/retention-law-articles/{$pivot->retention_id}/{$pivot->law_article_id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('retention_law_articles', [
            'retention_id' => $pivot->retention_id,
            'law_article_id' => $pivot->law_article_id,
        ]);
    }
}
