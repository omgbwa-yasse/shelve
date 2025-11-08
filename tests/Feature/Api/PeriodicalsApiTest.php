<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\RecordType;
use App\Models\Organisation;
use App\Models\RecordPeriodic;
use App\Models\RecordPeriodicIssue;
use App\Models\RecordPeriodicArticle;
use App\Models\RecordPeriodicSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class PeriodicalsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private RecordType $type;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->organisation = Organisation::factory()->create([
            'name' => 'Test Organisation',
        ]);

        $this->type = RecordType::factory()->create([
            'code' => 'PERIODICAL',
            'name' => 'Scientific Journal',
            'applies_to' => 'periodical',
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_list_periodicals()
    {
        RecordPeriodic::factory()->count(3)->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->getJson('/api/v1/periodics');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_show_periodical()
    {
        $periodic = RecordPeriodic::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'title' => 'Nature Magazine',
        ]);

        $response = $this->getJson("/api/v1/periodics/{$periodic->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $periodic->id,
                    'title' => 'Nature Magazine',
                ]
            ]);
    }

    public function test_can_create_periodical()
    {
        $data = [
            'code' => 'PER-001',
            'title' => 'Science Journal',
            'issn' => '1234-5678',
            'type_id' => $this->type->id,
            'publisher' => 'Academic Press',
            'frequency' => 'monthly',
        ];

        $response = $this->postJson('/api/v1/periodics', $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'code' => 'PER-001',
                    'title' => 'Science Journal',
                ]
            ]);
    }

    public function test_can_update_periodical()
    {
        $periodic = RecordPeriodic::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'frequency' => 'monthly',
        ]);

        $response = $this->putJson("/api/v1/periodics/{$periodic->id}", [
            'frequency' => 'quarterly',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('record_periodics', [
            'id' => $periodic->id,
            'frequency' => 'quarterly',
        ]);
    }

    public function test_can_delete_periodical()
    {
        $periodic = RecordPeriodic::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->deleteJson("/api/v1/periodics/{$periodic->id}");

        $response->assertStatus(204);
    }

    public function test_can_add_issue()
    {
        $periodic = RecordPeriodic::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->postJson("/api/v1/periodics/{$periodic->id}/issues", [
            'volume_number' => 10,
            'issue_number' => 3,
            'publication_date' => '2024-03-01',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('record_periodic_issues', [
            'periodic_id' => $periodic->id,
            'volume_number' => 10,
            'issue_number' => 3,
        ]);
    }

    public function test_can_add_article()
    {
        $periodic = RecordPeriodic::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $issue = RecordPeriodicIssue::factory()->create([
            'periodic_id' => $periodic->id,
        ]);

        $response = $this->postJson("/api/v1/periodics/{$periodic->id}/articles", [
            'issue_id' => $issue->id,
            'title' => 'Research Article',
            'authors' => 'John Doe, Jane Smith',
            'pages' => '1-10',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('record_periodic_articles', [
            'periodic_id' => $periodic->id,
            'title' => 'Research Article',
        ]);
    }

    public function test_can_create_subscription()
    {
        $periodic = RecordPeriodic::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->postJson("/api/v1/periodics/{$periodic->id}/subscriptions", [
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('record_periodic_subscriptions', [
            'periodic_id' => $periodic->id,
            'status' => 'active',
        ]);
    }

    public function test_can_search_periodicals()
    {
        RecordPeriodic::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'title' => 'Physics Review',
        ]);

        $response = $this->getJson('/api/v1/periodics-search?q=Physics');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Physics Review']);
    }

    public function test_can_search_issues()
    {
        $periodic = RecordPeriodic::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        RecordPeriodicIssue::factory()->create([
            'periodic_id' => $periodic->id,
            'volume_number' => 15,
        ]);

        $response = $this->getJson('/api/v1/periodics-issues-search?volume=15');

        $response->assertStatus(200);
    }

    public function test_can_search_articles()
    {
        $periodic = RecordPeriodic::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        RecordPeriodicArticle::factory()->create([
            'periodic_id' => $periodic->id,
            'title' => 'Quantum Mechanics Study',
        ]);

        $response = $this->getJson('/api/v1/periodics-articles-search?q=Quantum');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Quantum Mechanics Study']);
    }

    public function test_can_get_expiring_subscriptions()
    {
        $periodic = RecordPeriodic::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        RecordPeriodicSubscription::factory()->create([
            'periodic_id' => $periodic->id,
            'end_date' => now()->addDays(20),
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/periodics-subscriptions-expiring?days=30');

        $response->assertStatus(200);
    }

    public function test_can_get_statistics()
    {
        RecordPeriodic::factory()->count(5)->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->getJson('/api/v1/periodics-statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_periodics',
                    'total_issues',
                    'total_articles',
                    'active_subscriptions',
                ]
            ]);
    }

    public function test_requires_authentication()
    {
        $this->app['auth']->guard('sanctum')->forgetGuards();

        $response = $this->getJson('/api/v1/periodics');

        $response->assertStatus(401);
    }
}
