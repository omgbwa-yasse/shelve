<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Document;
use App\Models\Folder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DocumentsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test documents index page loads.
     */
    public function test_documents_index_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/documents')
                    ->assertSee('Documents')
                    ->assertSee('Upload Document')
                    ->assertSee('Search');
        });
    }

    /**
     * Test document creation form displays.
     */
    public function test_create_document_form_displays(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/documents/create')
                    ->assertSee('Create Document')
                    ->assertSee('Title')
                    ->assertSee('Description')
                    ->assertSee('Folder')
                    ->assertSee('Upload File');
        });
    }

    /**
     * Test can create document with metadata.
     */
    public function test_can_create_document(): void
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $folder) {
            $browser->loginAs($user)
                    ->visit('/documents/create')
                    ->type('title', 'Test Document')
                    ->type('description', 'Test description')
                    ->select('folder_id', $folder->id)
                    ->attach('file', __DIR__ . '/../../storage/app/test.pdf')
                    ->press('Create Document')
                    ->pause(1000)
                    ->assertPathIs('/documents')
                    ->assertSee('Document created successfully')
                    ->assertSee('Test Document');
        });
    }

    /**
     * Test FilePond file upload interface.
     */
    public function test_filepond_upload_interface(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/documents/create')
                    ->assertPresent('.filepond')
                    ->assertSee('Drag & Drop your files or Browse');
        });
    }

    /**
     * Test document validation.
     */
    public function test_document_validation_errors(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/documents/create')
                    ->press('Create Document')
                    ->pause(300)
                    ->assertSee('The title field is required');
        });
    }

    /**
     * Test can view document details.
     */
    public function test_can_view_document_details(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'title' => 'Test Doc',
            'user_id' => $user->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $document) {
            $browser->loginAs($user)
                    ->visit("/documents/{$document->id}")
                    ->assertSee('Test Doc')
                    ->assertSee('Description')
                    ->assertSee('Metadata')
                    ->assertSee('Versions');
        });
    }

    /**
     * Test PDF preview displays.
     */
    public function test_pdf_preview_displays(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'title' => 'PDF Document',
            'file_path' => 'documents/test.pdf',
            'mime_type' => 'application/pdf',
            'user_id' => $user->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $document) {
            $browser->loginAs($user)
                    ->visit("/documents/{$document->id}")
                    ->pause(1000)
                    ->assertPresent('#pdf-viewer')
                    ->assertSee('PDF Preview');
        });
    }

    /**
     * Test can edit document.
     */
    public function test_can_edit_document(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'title' => 'Original Title',
            'user_id' => $user->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $document) {
            $browser->loginAs($user)
                    ->visit("/documents/{$document->id}/edit")
                    ->assertInputValue('title', 'Original Title')
                    ->clear('title')
                    ->type('title', 'Updated Title')
                    ->press('Update Document')
                    ->pause(500)
                    ->assertPathIs('/documents/' . $document->id)
                    ->assertSee('Document updated successfully')
                    ->assertSee('Updated Title');
        });
    }

    /**
     * Test document versioning.
     */
    public function test_document_versioning(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $document) {
            $browser->loginAs($user)
                    ->visit("/documents/{$document->id}")
                    ->click('@versions-tab')
                    ->pause(500)
                    ->assertSee('Versions')
                    ->assertSee('Upload New Version');
        });
    }

    /**
     * Test document approval workflow.
     */
    public function test_document_approval_workflow(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $document = Document::factory()->create([
            'status' => 'pending',
            'user_id' => $user->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $document) {
            $browser->loginAs($user)
                    ->visit("/documents/{$document->id}")
                    ->assertSee('Status: Pending')
                    ->press('@approve-button')
                    ->pause(500)
                    ->assertSee('Document approved successfully')
                    ->assertSee('Status: Approved');
        });
    }

    /**
     * Test document search and filters.
     */
    public function test_document_search_and_filters(): void
    {
        $user = User::factory()->create();
        Document::factory()->create(['title' => 'Report 2024', 'user_id' => $user->id]);
        Document::factory()->create(['title' => 'Letter 2024', 'user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/documents')
                    ->type('@search-input', 'Report')
                    ->pause(500)
                    ->assertSee('Report 2024')
                    ->assertDontSee('Letter 2024');
        });
    }

    /**
     * Test can delete document.
     */
    public function test_can_delete_document(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'title' => 'To Delete',
            'user_id' => $user->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $document) {
            $browser->loginAs($user)
                    ->visit("/documents/{$document->id}")
                    ->press('@delete-button')
                    ->pause(500)
                    ->whenAvailable('@confirm-dialog', function ($dialog) {
                        $dialog->press('Confirm');
                    })
                    ->pause(500)
                    ->assertPathIs('/documents')
                    ->assertSee('Document deleted successfully');
        });
    }
}
