<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\RecordType;
use App\Models\Organisation;
use App\Models\RecordArtifact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ArtifactsApiTest extends TestCase
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
            'name' => 'Test Museum',
        ]);

        $this->type = RecordType::factory()->create([
            'code' => 'ARTIFACT',
            'name' => 'Museum Artifact',
            'applies_to' => 'artifact',
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_list_artifacts()
    {
        RecordArtifact::factory()->count(3)->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->getJson('/api/v1/artifacts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_show_artifact()
    {
        $artifact = RecordArtifact::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'name' => 'Ancient Vase',
        ]);

        $response = $this->getJson("/api/v1/artifacts/{$artifact->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $artifact->id,
                    'name' => 'Ancient Vase',
                ]
            ]);
    }

    public function test_can_create_artifact()
    {
        $data = [
            'code' => 'ART-001',
            'name' => 'Sculpture',
            'description' => 'Ancient sculpture',
            'type_id' => $this->type->id,
            'category' => 'sculpture',
            'materials' => 'Bronze',
            'dimensions' => '50x30x20 cm',
        ];

        $response = $this->postJson('/api/v1/artifacts', $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'code' => 'ART-001',
                    'name' => 'Sculpture',
                ]
            ]);
    }

    public function test_can_update_artifact()
    {
        $artifact = RecordArtifact::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'conservation_state' => 'good',
        ]);

        $response = $this->putJson("/api/v1/artifacts/{$artifact->id}", [
            'conservation_state' => 'excellent',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('record_artifacts', [
            'id' => $artifact->id,
            'conservation_state' => 'excellent',
        ]);
    }

    public function test_can_delete_artifact()
    {
        $artifact = RecordArtifact::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->deleteJson("/api/v1/artifacts/{$artifact->id}");

        $response->assertStatus(204);
    }

    public function test_can_loan_artifact()
    {
        $artifact = RecordArtifact::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'is_on_loan' => false,
        ]);

        $response = $this->postJson("/api/v1/artifacts/{$artifact->id}/loans", [
            'borrower' => 'Partner Museum',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('record_artifacts', [
            'id' => $artifact->id,
            'is_on_loan' => true,
        ]);
    }

    public function test_can_return_from_loan()
    {
        $artifact = RecordArtifact::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'is_on_loan' => true,
        ]);

        $response = $this->postJson("/api/v1/artifacts/{$artifact->id}/return", [
            'return_date' => now()->toDateString(),
            'condition' => 'good',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('record_artifacts', [
            'id' => $artifact->id,
            'is_on_loan' => false,
        ]);
    }

    public function test_can_add_condition_report()
    {
        $artifact = RecordArtifact::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->postJson("/api/v1/artifacts/{$artifact->id}/condition-reports", [
            'report' => 'Minor wear on surface',
            'condition' => 'good',
        ]);

        $response->assertStatus(200);
    }

    public function test_can_update_valuation()
    {
        $artifact = RecordArtifact::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'estimated_value' => 10000,
        ]);

        $response = $this->postJson("/api/v1/artifacts/{$artifact->id}/valuations", [
            'estimated_value' => 15000,
            'insurance_value' => 20000,
            'valuation_date' => now()->toDateString(),
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('record_artifacts', [
            'id' => $artifact->id,
            'estimated_value' => 15000,
        ]);
    }

    public function test_can_search_artifacts()
    {
        RecordArtifact::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'name' => 'Golden Mask',
        ]);

        $response = $this->getJson('/api/v1/artifacts-search?q=Golden');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Golden Mask']);
    }

    public function test_can_get_statistics()
    {
        RecordArtifact::factory()->count(5)->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->getJson('/api/v1/artifacts-statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total',
                    'on_display',
                    'on_loan',
                ]
            ]);
    }

    public function test_requires_authentication()
    {
        $this->app['auth']->guard('sanctum')->forgetGuards();

        $response = $this->getJson('/api/v1/artifacts');

        $response->assertStatus(401);
    }
}
