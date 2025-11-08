<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Artifact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtifactApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test get all artifacts.
     */
    public function test_can_get_all_artifacts(): void
    {
        Artifact::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/artifacts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'category', 'created_at']
                ]
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * Test get single artifact.
     */
    public function test_can_get_single_artifact(): void
    {
        $artifact = Artifact::factory()->create([
            'name' => 'Ancient Vase',
            'user_id' => $this->user->id
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/artifacts/{$artifact->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Ancient Vase']);
    }

    /**
     * Test create artifact.
     */
    public function test_can_create_artifact(): void
    {
        $data = [
            'name' => 'Bronze Statue',
            'description' => 'Ancient bronze statue',
            'category' => 'Sculpture',
            'date_of_origin' => '100 BC',
            'material' => 'Bronze'
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/artifacts', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Bronze Statue']);

        $this->assertDatabaseHas('artifacts', ['name' => 'Bronze Statue']);
    }

    /**
     * Test create artifact validation.
     */
    public function test_create_artifact_validation_fails(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/artifacts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test update artifact.
     */
    public function test_can_update_artifact(): void
    {
        $artifact = Artifact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->putJson("/api/v1/artifacts/{$artifact->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated description'
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);
    }

    /**
     * Test delete artifact.
     */
    public function test_can_delete_artifact(): void
    {
        $artifact = Artifact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/artifacts/{$artifact->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('artifacts', ['id' => $artifact->id]);
    }

    /**
     * Test upload artifact images.
     */
    public function test_can_upload_artifact_images(): void
    {
        $artifact = Artifact::factory()->create(['user_id' => $this->user->id]);
        $image = UploadedFile::fake()->image('artifact.jpg', 800, 600);

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/artifacts/{$artifact->id}/images", [
                'image' => $image
            ]);

        $response->assertStatus(200);
        Storage::disk('local')->assertExists('artifacts/images/' . $image->hashName());
    }

    /**
     * Test get artifact exhibitions.
     */
    public function test_can_get_artifact_exhibitions(): void
    {
        $artifact = Artifact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/artifacts/{$artifact->id}/exhibitions");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /**
     * Test add artifact to exhibition.
     */
    public function test_can_add_artifact_to_exhibition(): void
    {
        $artifact = Artifact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/artifacts/{$artifact->id}/exhibitions", [
                'exhibition_name' => 'Ancient Rome',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31'
            ]);

        $response->assertStatus(201);
    }

    /**
     * Test get artifact loans.
     */
    public function test_can_get_artifact_loans(): void
    {
        $artifact = Artifact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/artifacts/{$artifact->id}/loans");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /**
     * Test create artifact loan.
     */
    public function test_can_create_artifact_loan(): void
    {
        $artifact = Artifact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/artifacts/{$artifact->id}/loans", [
                'borrower_name' => 'Museum of History',
                'loan_date' => '2024-03-01',
                'return_date' => '2024-06-01',
                'purpose' => 'Exhibition'
            ]);

        $response->assertStatus(201);
    }

    /**
     * Test filter artifacts by category.
     */
    public function test_can_filter_artifacts_by_category(): void
    {
        Artifact::factory()->create([
            'name' => 'Sculpture Item',
            'category' => 'Sculpture',
            'user_id' => $this->user->id
        ]);
        Artifact::factory()->create([
            'name' => 'Painting Item',
            'category' => 'Painting',
            'user_id' => $this->user->id
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/artifacts?category=Sculpture');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Sculpture Item'])
            ->assertJsonMissing(['name' => 'Painting Item']);
    }

    /**
     * Test unauthenticated access fails.
     */
    public function test_unauthenticated_access_fails(): void
    {
        $response = $this->getJson('/api/v1/artifacts');
        $response->assertStatus(401);
    }
}
