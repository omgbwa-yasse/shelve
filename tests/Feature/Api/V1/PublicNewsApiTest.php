<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\PublicNews;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * D15 — news du portail public (lecture publique, guard public).
 */
class PublicNewsApiTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAuthor(): User
    {
        return User::factory()->forOrganisation(Organisation::factory()->create())->create();
    }

    private function makeNews(array $overrides = []): PublicNews
    {
        // `is_published` n'est pas dans $fillable : forceFill est nécessaire
        // pour le positionner (le modèle le lira ensuite via l'accesseur).
        $news = new PublicNews;
        $news->forceFill(array_merge([
            'name' => 'Actualité',
            'title' => 'Une actualité',
            'slug' => 'une-actualite-' . uniqid(),
            'content' => 'Contenu de l\'actualité.',
            'summary' => 'Résumé.',
            'author_id' => $this->makeAuthor()->id,
            'is_published' => true,
            'status' => 'published',
            'featured' => false,
            'published_at' => now(),
        ], $overrides))->save();

        return $news;
    }

    public function test_index_est_public(): void
    {
        $this->getJson('/api/public/news')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total'], 'links']);
    }

    public function test_index_ne_retourne_que_les_news_publiees(): void
    {
        $this->makeNews();
        $this->makeNews(['is_published' => false, 'status' => 'draft']);

        $this->getJson('/api/public/news')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Une actualité');
    }

    public function test_index_filtre_par_recherche(): void
    {
        $this->makeNews(['title' => 'Inauguration du fonds']);
        $this->makeNews(['title' => 'Rapport annuel']);

        $this->getJson('/api/public/news?search=inauguration')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Inauguration du fonds');
    }

    public function test_show_retourne_une_news_publiee(): void
    {
        $news = $this->makeNews(['featured' => true]);

        $this->getJson("/api/public/news/{$news->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $news->id)
            ->assertJsonPath('data.featured', true)
            ->assertJsonPath('data.author.name', $news->author->name);
    }

    public function test_show_refuse_une_news_non_publiee(): void
    {
        $news = $this->makeNews(['is_published' => false, 'status' => 'draft']);

        $this->getJson("/api/public/news/{$news->id}")->assertStatus(404);
    }

    public function test_show_inconnu_renvoie_404(): void
    {
        $this->getJson('/api/public/news/999999')->assertStatus(404);
    }

    public function test_latest_retourne_les_dernieres_news(): void
    {
        $this->makeNews(['title' => 'A']);
        $this->makeNews(['title' => 'B']);

        $this->getJson('/api/public/news/latest?limit=1')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
