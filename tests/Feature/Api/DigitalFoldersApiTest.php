<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Type;
use App\Models\Organisation;
use App\Models\RecordDigitalFolder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class DigitalFoldersApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Type $type;
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

        $this->type = Type::factory()->create([
            'code' => 'DIGITAL_FOLDER',
            'name' => 'Digital Folder',
            'applies_to' => 'folder',
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_list_folders()
    {
        RecordDigitalFolder::factory()->count(3)->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->getJson('/api/v1/digital-folders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'name', 'type', 'creator', 'dates']
                ]
            ]);
    }

    public function test_can_show_folder()
    {
        $folder = RecordDigitalFolder::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'name' => 'Test Folder',
        ]);

        $response = $this->getJson("/api/v1/digital-folders/{$folder->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $folder->id,
                    'name' => 'Test Folder',
                ]
            ]);
    }

    public function test_can_create_folder()
    {
        $data = [
            'code' => 'FOL-001',
            'name' => 'New Folder',
            'description' => 'Test folder description',
            'type_id' => $this->type->id,
            'access_level' => 'public',
        ];

        $response = $this->postJson('/api/v1/digital-folders', $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'code' => 'FOL-001',
                    'name' => 'New Folder',
                ]
            ]);

        $this->assertDatabaseHas('record_digital_folders', [
            'code' => 'FOL-001',
            'name' => 'New Folder',
        ]);
    }

    public function test_can_update_folder()
    {
        $folder = RecordDigitalFolder::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'name' => 'Original Name',
        ]);

        $response = $this->putJson("/api/v1/digital-folders/{$folder->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Name',
                ]
            ]);

        $this->assertDatabaseHas('record_digital_folders', [
            'id' => $folder->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_folder()
    {
        $folder = RecordDigitalFolder::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->deleteJson("/api/v1/digital-folders/{$folder->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('record_digital_folders', [
            'id' => $folder->id,
        ]);
    }

    public function test_can_get_folder_tree()
    {
        $parent = RecordDigitalFolder::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        RecordDigitalFolder::factory()->count(2)->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'parent_id' => $parent->id,
        ]);

        $response = $this->getJson("/api/v1/digital-folders/{$parent->id}/tree");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'children' => [
                        '*' => ['id', 'name']
                    ]
                ]
            ]);
    }

    public function test_can_move_folder()
    {
        $sourceFolder = RecordDigitalFolder::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
            'parent_id' => null,
        ]);

        $targetFolder = RecordDigitalFolder::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->postJson("/api/v1/digital-folders/{$sourceFolder->id}/move", [
            'target_folder_id' => $targetFolder->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('record_digital_folders', [
            'id' => $sourceFolder->id,
            'parent_id' => $targetFolder->id,
        ]);
    }

    public function test_can_get_folder_statistics()
    {
        $folder = RecordDigitalFolder::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'type_id' => $this->type->id,
        ]);

        $response = $this->getJson("/api/v1/digital-folders/{$folder->id}/statistics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'documents_count',
                    'subfolders_count',
                    'total_size',
                ]
            ]);
    }

    public function test_requires_authentication()
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/v1/digital-folders');

        $response->assertStatus(401);
    }

    public function test_validates_folder_creation()
    {
        $response = $this->postJson('/api/v1/digital-folders', [
            'name' => '', // Empty name should fail
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
