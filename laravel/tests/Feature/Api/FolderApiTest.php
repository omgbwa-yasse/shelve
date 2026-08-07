<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderApiTest extends TestCase
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
     * Test get all folders.
     */
    public function test_can_get_all_folders(): void
    {
        Folder::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/folders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'parent_id', 'created_at', 'updated_at']
                ]
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * Test get single folder.
     */
    public function test_can_get_single_folder(): void
    {
        $folder = Folder::factory()->create([
            'name' => 'Test Folder',
            'user_id' => $this->user->id
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/folders/{$folder->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test Folder']);
    }

    /**
     * Test create folder.
     */
    public function test_can_create_folder(): void
    {
        $data = [
            'name' => 'New Folder',
            'description' => 'Test description',
            'parent_id' => null
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/folders', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'New Folder']);

        $this->assertDatabaseHas('folders', ['name' => 'New Folder']);
    }

    /**
     * Test create folder validation fails.
     */
    public function test_create_folder_validation_fails(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/folders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test update folder.
     */
    public function test_can_update_folder(): void
    {
        $folder = Folder::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->putJson("/api/v1/folders/{$folder->id}", [
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);
    }

    /**
     * Test delete folder.
     */
    public function test_can_delete_folder(): void
    {
        $folder = Folder::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/folders/{$folder->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('folders', ['id' => $folder->id]);
    }

    /**
     * Test get folder tree.
     */
    public function test_can_get_folder_tree(): void
    {
        Folder::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/folders/tree');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /**
     * Test move folder.
     */
    public function test_can_move_folder(): void
    {
        $parent = Folder::factory()->create(['user_id' => $this->user->id]);
        $folder = Folder::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/folders/{$folder->id}/move", [
                'parent_id' => $parent->id
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('folders', [
            'id' => $folder->id,
            'parent_id' => $parent->id
        ]);
    }

    /**
     * Test unauthenticated access fails.
     */
    public function test_unauthenticated_access_fails(): void
    {
        $response = $this->getJson('/api/v1/folders');
        $response->assertStatus(401);
    }

    /**
     * Test unauthorized access fails.
     */
    public function test_unauthorized_folder_access_fails(): void
    {
        $otherUser = User::factory()->create();
        $folder = Folder::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/folders/{$folder->id}");

        $response->assertStatus(403);
    }
}
