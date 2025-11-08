<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Document;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentApiTest extends TestCase
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
     * Test get all documents.
     */
    public function test_can_get_all_documents(): void
    {
        Document::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/documents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'file_path', 'created_at']
                ]
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * Test get single document.
     */
    public function test_can_get_single_document(): void
    {
        $document = Document::factory()->create([
            'title' => 'Test Document',
            'user_id' => $this->user->id
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Test Document']);
    }

    /**
     * Test create document.
     */
    public function test_can_create_document(): void
    {
        $folder = Folder::factory()->create(['user_id' => $this->user->id]);
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/documents', [
                'title' => 'New Document',
                'description' => 'Test description',
                'folder_id' => $folder->id,
                'file' => $file
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Document']);

        $this->assertDatabaseHas('documents', ['title' => 'New Document']);
    }

    /**
     * Test create document validation.
     */
    public function test_create_document_validation_fails(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/documents', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test update document.
     */
    public function test_can_update_document(): void
    {
        $document = Document::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->putJson("/api/v1/documents/{$document->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated description'
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title']);
    }

    /**
     * Test delete document.
     */
    public function test_can_delete_document(): void
    {
        $document = Document::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('documents', ['id' => $document->id]);
    }

    /**
     * Test upload document file.
     */
    public function test_can_upload_document_file(): void
    {
        $document = Document::factory()->create(['user_id' => $this->user->id]);
        $file = UploadedFile::fake()->create('new.pdf', 200, 'application/pdf');

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/documents/{$document->id}/upload", [
                'file' => $file
            ]);

        $response->assertStatus(200);
        Storage::disk('local')->assertExists('documents/' . $file->hashName());
    }

    /**
     * Test approve document.
     */
    public function test_can_approve_document(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = $admin->createToken('admin-token')->plainTextToken;
        $document = Document::factory()->create([
            'status' => 'pending',
            'user_id' => $this->user->id
        ]);

        $response = $this->withToken($adminToken)
            ->postJson("/api/v1/documents/{$document->id}/approve");

        $response->assertStatus(200);
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'approved'
        ]);
    }

    /**
     * Test reject document.
     */
    public function test_can_reject_document(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = $admin->createToken('admin-token')->plainTextToken;
        $document = Document::factory()->create([
            'status' => 'pending',
            'user_id' => $this->user->id
        ]);

        $response = $this->withToken($adminToken)
            ->postJson("/api/v1/documents/{$document->id}/reject", [
                'reason' => 'Invalid content'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'rejected'
        ]);
    }

    /**
     * Test get document versions.
     */
    public function test_can_get_document_versions(): void
    {
        $document = Document::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/documents/{$document->id}/versions");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /**
     * Test download document version.
     */
    public function test_can_download_document_version(): void
    {
        $document = Document::factory()->create([
            'file_path' => 'documents/test.pdf',
            'user_id' => $this->user->id
        ]);
        Storage::put('documents/test.pdf', 'test content');

        $response = $this->withToken($this->token)
            ->get("/api/v1/documents/{$document->id}/versions/1/download");

        $response->assertStatus(200);
    }

    /**
     * Test search documents.
     */
    public function test_can_search_documents(): void
    {
        Document::factory()->create([
            'title' => 'Annual Report 2024',
            'user_id' => $this->user->id
        ]);
        Document::factory()->create([
            'title' => 'Meeting Notes',
            'user_id' => $this->user->id
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/documents?search=Report');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Annual Report 2024'])
            ->assertJsonMissing(['title' => 'Meeting Notes']);
    }

    /**
     * Test unauthenticated access fails.
     */
    public function test_unauthenticated_access_fails(): void
    {
        $response = $this->getJson('/api/v1/documents');
        $response->assertStatus(401);
    }
}
