<?php

namespace Tests\Performance;

use App\Models\User;
use App\Models\Folder;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabasePerformanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test N+1 query detection for folders.
     */
    public function test_folders_index_avoids_n_plus_one_queries(): void
    {
        Folder::factory()->count(20)->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user);

        // Count queries
        \DB::enableQueryLog();

        $response = $this->get('/folders');

        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        // Should use eager loading - expect less than 5 queries
        $this->assertLessThan(5, count($queries),
            'Too many queries detected. N+1 problem possible.');

        $response->assertStatus(200);
    }

    /**
     * Test document listing performance.
     */
    public function test_documents_index_is_performant(): void
    {
        Document::factory()->count(50)->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user);

        $startTime = microtime(true);
        $response = $this->get('/documents');
        $endTime = microtime(true);

        $executionTime = ($endTime - $startTime) * 1000; // Convert to ms

        // Should load in less than 500ms
        $this->assertLessThan(500, $executionTime,
            "Documents page took {$executionTime}ms - too slow");

        $response->assertStatus(200);
    }

    /**
     * Test API response time.
     */
    public function test_api_response_time_is_acceptable(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        Folder::factory()->count(10)->create(['user_id' => $this->user->id]);

        $startTime = microtime(true);
        $response = $this->withToken($token)->getJson('/api/v1/folders');
        $endTime = microtime(true);

        $executionTime = ($endTime - $startTime) * 1000;

        // API should respond in less than 200ms
        $this->assertLessThan(200, $executionTime,
            "API took {$executionTime}ms - too slow");

        $response->assertStatus(200);
    }

    /**
     * Test eager loading for document with folder.
     */
    public function test_document_with_folder_uses_eager_loading(): void
    {
        $folder = Folder::factory()->create(['user_id' => $this->user->id]);
        Document::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'folder_id' => $folder->id
        ]);

        $this->actingAs($this->user);

        \DB::enableQueryLog();
        $response = $this->get('/documents');
        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        // Should use eager loading for folders
        $this->assertLessThan(3, count($queries),
            'Documents should eager load folders to avoid N+1');

        $response->assertStatus(200);
    }

    /**
     * Test pagination performance.
     */
    public function test_pagination_is_efficient(): void
    {
        Folder::factory()->count(100)->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user);

        $startTime = microtime(true);
        $response = $this->get('/folders?page=5&per_page=20');
        $endTime = microtime(true);

        $executionTime = ($endTime - $startTime) * 1000;

        // Pagination should be fast even with many records
        $this->assertLessThan(300, $executionTime);

        $response->assertStatus(200);
    }

    /**
     * Test search performance.
     */
    public function test_search_is_performant(): void
    {
        Document::factory()->count(100)->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user);

        $startTime = microtime(true);
        $response = $this->get('/documents?search=test');
        $endTime = microtime(true);

        $executionTime = ($endTime - $startTime) * 1000;

        // Search should complete in reasonable time
        $this->assertLessThan(400, $executionTime,
            "Search took {$executionTime}ms - consider indexing");

        $response->assertStatus(200);
    }

    /**
     * Test database index usage.
     */
    public function test_queries_use_database_indexes(): void
    {
        Folder::factory()->count(50)->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user);

        \DB::enableQueryLog();
        $this->get('/folders');
        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        // Check that queries use WHERE clauses on indexed columns
        foreach ($queries as $query) {
            if (str_contains($query['query'], 'folders')) {
                // Should filter by user_id or id (indexed columns)
                $this->assertTrue(
                    str_contains($query['query'], 'user_id') ||
                    str_contains($query['query'], 'id'),
                    'Queries should use indexed columns'
                );
            }
        }
    }
}
