<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\RecordPeriodical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodicalApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test get all periodicals.
     */
    public function test_can_get_all_periodicals(): void
    {
        RecordPeriodical::factory()->count(5)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/periodicals');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'issn', 'publisher', 'created_at']
                ]
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * Test get single periodical.
     */
    public function test_can_get_single_periodical(): void
    {
        $periodical = RecordPeriodical::factory()->create([
            'title' => 'Science Monthly',
            'issn' => '1234-5678'
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/periodicals/{$periodical->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Science Monthly']);
    }

    /**
     * Test search periodicals by title.
     */
    public function test_can_search_periodicals_by_title(): void
    {
        RecordPeriodical::factory()->create(['title' => 'Nature Magazine']);
        RecordPeriodical::factory()->create(['title' => 'Science Journal']);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/periodicals?search=Nature');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Nature Magazine'])
            ->assertJsonMissing(['title' => 'Science Journal']);
    }

    /**
     * Test filter periodicals by publisher.
     */
    public function test_can_filter_by_publisher(): void
    {
        RecordPeriodical::factory()->create([
            'title' => 'Journal A',
            'publisher' => 'Oxford Press'
        ]);
        RecordPeriodical::factory()->create([
            'title' => 'Journal B',
            'publisher' => 'Cambridge Press'
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/periodicals?publisher=Oxford Press');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Journal A'])
            ->assertJsonMissing(['title' => 'Journal B']);
    }

    /**
     * Test get periodical issues.
     */
    public function test_can_get_periodical_issues(): void
    {
        $periodical = RecordPeriodical::factory()->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/periodicals/{$periodical->id}/issues");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /**
     * Test get periodical articles.
     */
    public function test_can_get_periodical_articles(): void
    {
        $periodical = RecordPeriodical::factory()->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/periodicals/{$periodical->id}/articles");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /**
     * Test search articles within periodical.
     */
    public function test_can_search_articles_within_periodical(): void
    {
        $periodical = RecordPeriodical::factory()->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/periodicals/{$periodical->id}/articles?search=climate");

        $response->assertStatus(200);
    }

    /**
     * Test filter by ISSN.
     */
    public function test_can_filter_by_issn(): void
    {
        RecordPeriodical::factory()->create([
            'title' => 'Tech Review',
            'issn' => '1111-2222'
        ]);
        RecordPeriodical::factory()->create([
            'title' => 'Health Today',
            'issn' => '3333-4444'
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/periodicals?issn=1111-2222');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Tech Review'])
            ->assertJsonMissing(['title' => 'Health Today']);
    }

    /**
     * Test pagination works.
     */
    public function test_pagination_works(): void
    {
        RecordPeriodical::factory()->count(30)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/periodicals?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'total', 'per_page']
            ])
            ->assertJsonCount(10, 'data');
    }

    /**
     * Test unauthenticated access fails.
     */
    public function test_unauthenticated_access_fails(): void
    {
        $response = $this->getJson('/api/v1/periodicals');
        $response->assertStatus(401);
    }
}
