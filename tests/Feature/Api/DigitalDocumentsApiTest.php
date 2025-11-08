<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\RecordType;
use App\Models\Organisation;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use App\Models\Attachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class DigitalDocumentsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private RecordType $type;
    private Organisation $organisation;
    private RecordDigitalFolder $folder;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->organisation = Organisation::factory()->create([
            'name' => 'Test Organisation',
        ]);

        $this->type = RecordType::factory()->create([
            'code' => 'DOCUMENT',
            'name' => 'Document',
            'applies_to' => 'document',
        ]);

        $this->folder = RecordDigitalFolder::factory()->create([
            'creator_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_list_documents()
    {
        RecordDigitalDocument::factory()->count(3)->create([
            'creator_id' => $this->user->id,
            'type_id' => $this->type->id,
            'folder_id' => $this->folder->id,
        ]);

        $response = $this->getJson('/api/v1/digital-documents');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_show_document()
    {
        $document = RecordDigitalDocument::factory()->create([
            'creator_id' => $this->user->id,
            'type_id' => $this->type->id,
            'folder_id' => $this->folder->id,
            'name' => 'Test Document',
        ]);

        $response = $this->getJson("/api/v1/digital-documents/{$document->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $document->id,
                    'name' => 'Test Document',
                ]
            ]);
    }

    public function test_can_create_document()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $data = [
            'code' => 'DOC-001',
            'name' => 'New Document',
            'description' => 'Test document',
            'type_id' => $this->type->id,
            'folder_id' => $this->folder->id,
            'file' => $file,
        ];

        $response = $this->postJson('/api/v1/digital-documents', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('record_digital_documents', [
            'code' => 'DOC-001',
            'name' => 'New Document',
        ]);
    }

    public function test_can_update_document()
    {
        $document = RecordDigitalDocument::factory()->create([
            'creator_id' => $this->user->id,
            'type_id' => $this->type->id,
            'folder_id' => $this->folder->id,
            'name' => 'Original Name',
        ]);

        $response = $this->putJson("/api/v1/digital-documents/{$document->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Name',
                ]
            ]);
    }

    public function test_can_delete_document()
    {
        $document = RecordDigitalDocument::factory()->create([
            'creator_id' => $this->user->id,
            'type_id' => $this->type->id,
            'folder_id' => $this->folder->id,
        ]);

        $response = $this->deleteJson("/api/v1/digital-documents/{$document->id}");

        $response->assertStatus(204);
    }

    public function test_can_create_version()
    {
        $document = RecordDigitalDocument::factory()->create([
            'creator_id' => $this->user->id,
            'type_id' => $this->type->id,
            'folder_id' => $this->folder->id,
            'version_number' => 1,
        ]);

        $file = UploadedFile::fake()->create('new-version.pdf', 1500);

        $response = $this->postJson("/api/v1/digital-documents/{$document->id}/versions", [
            'file' => $file,
            'comment' => 'Version 2',
        ]);

        $response->assertStatus(201);
    }

    public function test_can_submit_for_approval()
    {
        $document = RecordDigitalDocument::factory()->create([
            'creator_id' => $this->user->id,
            'type_id' => $this->type->id,
            'folder_id' => $this->folder->id,
            'status' => 'draft',
        ]);

        $approver = User::factory()->create();

        $response = $this->postJson("/api/v1/digital-documents/{$document->id}/submit-approval", [
            'approver_id' => $approver->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('record_digital_documents', [
            'id' => $document->id,
            'status' => 'pending_approval',
        ]);
    }

    public function test_can_approve_document()
    {
        $document = RecordDigitalDocument::factory()->create([
            'creator_id' => $this->user->id,
            'type_id' => $this->type->id,
            'folder_id' => $this->folder->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->postJson("/api/v1/digital-documents/{$document->id}/approve", [
            'comment' => 'Approved',
        ]);

        $response->assertStatus(200);
    }

    public function test_can_reject_document()
    {
        $document = RecordDigitalDocument::factory()->create([
            'creator_id' => $this->user->id,
            'type_id' => $this->type->id,
            'folder_id' => $this->folder->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->postJson("/api/v1/digital-documents/{$document->id}/reject", [
            'comment' => 'Needs revision',
        ]);

        $response->assertStatus(200);
    }

    public function test_can_search_documents()
    {
        RecordDigitalDocument::factory()->create([
            'creator_id' => $this->user->id,
            'type_id' => $this->type->id,
            'folder_id' => $this->folder->id,
            'name' => 'Searchable Document',
        ]);

        $response = $this->getJson('/api/v1/digital-documents-search?q=Searchable');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Searchable Document']);
    }

    public function test_requires_authentication()
    {
        $this->app['auth']->guard('sanctum')->forgetGuards();

        $response = $this->getJson('/api/v1/digital-documents');

        $response->assertStatus(401);
    }
}
