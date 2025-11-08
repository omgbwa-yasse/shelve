<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttachmentExtensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_digital_document_attachment(): void
    {
        $user = User::factory()->create();

        $attachment = Attachment::create([
            'name' => 'Contrat.pdf',
            'description' => 'Contrat commercial 2025',
            'path' => 'documents/2025/',
            'crypt' => 'abc123xyz',
            'type' => Attachment::TYPE_DIGITAL_DOCUMENT,
            'mime_type' => 'application/pdf',
            'file_extension' => 'pdf',
            'size' => 1024000,
            'is_primary' => true,
            'page_count' => 15,
            'creator_id' => $user->id,
        ]);

        $this->assertDatabaseHas('attachments', [
            'name' => 'Contrat.pdf',
            'type' => 'digital_document',
            'is_primary' => true,
            'page_count' => 15,
        ]);

        $this->assertEquals('digital_document', $attachment->type);
        $this->assertTrue($attachment->is_primary);
        $this->assertEquals(15, $attachment->page_count);
    }

    public function test_can_set_ocr_metadata(): void
    {
        $user = User::factory()->create();

        $attachment = Attachment::create([
            'name' => 'Document scanné.pdf',
            'path' => 'scans/',
            'crypt' => 'xyz789',
            'type' => Attachment::TYPE_DIGITAL_DOCUMENT,
            'mime_type' => 'application/pdf',
            'size' => 500000,
            'content_text' => 'Texte extrait du document',
            'ocr_language' => 'fr',
            'ocr_confidence' => 98.75,
            'creator_id' => $user->id,
        ]);

        $this->assertEquals('fr', $attachment->ocr_language);
        $this->assertEquals(98.75, $attachment->ocr_confidence);
        $this->assertIsFloat($attachment->ocr_confidence);
    }

    public function test_primary_scope_filters_correctly(): void
    {
        $user = User::factory()->create();

        Attachment::create([
            'name' => 'Primary Doc.pdf',
            'path' => 'docs/',
            'crypt' => 'pri123',
            'is_primary' => true,
            'type' => 'digital_document',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'creator_id' => $user->id,
        ]);

        Attachment::create([
            'name' => 'Secondary Doc.pdf',
            'path' => 'docs/',
            'crypt' => 'sec456',
            'is_primary' => false,
            'type' => 'digital_document',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'creator_id' => $user->id,
        ]);

        Attachment::create([
            'name' => 'Primary Artifact.jpg',
            'path' => 'images/',
            'crypt' => 'art789',
            'is_primary' => true,
            'type' => 'artifact',
            'mime_type' => 'image/jpeg',
            'size' => 100,
            'creator_id' => $user->id,
        ]);

        $primaryDocs = Attachment::ofType('digital_document')->primary()->get();

        $this->assertCount(1, $primaryDocs);
        $this->assertTrue($primaryDocs->first()->is_primary);
        $this->assertEquals('digital_document', $primaryDocs->first()->type);
    }

    public function test_file_size_human_accessor(): void
    {
        $user = User::factory()->create();

        $attachment = Attachment::create([
            'name' => 'Large File.pdf',
            'path' => 'docs/',
            'crypt' => 'large123',
            'type' => 'digital_document',
            'mime_type' => 'application/pdf',
            'size' => 1048576, // 1 MB
            'creator_id' => $user->id,
        ]);

        $this->assertEquals('1 MB', $attachment->file_size_human);
    }

    public function test_ordered_by_display_scope(): void
    {
        $user = User::factory()->create();

        $attachment1 = Attachment::create([
            'name' => 'Third',
            'path' => 'docs/',
            'crypt' => 'third',
            'type' => 'digital_document',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'display_order' => 3,
            'creator_id' => $user->id,
        ]);

        $attachment2 = Attachment::create([
            'name' => 'First',
            'path' => 'docs/',
            'crypt' => 'first',
            'type' => 'digital_document',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'display_order' => 1,
            'creator_id' => $user->id,
        ]);

        $attachment3 = Attachment::create([
            'name' => 'Second',
            'path' => 'docs/',
            'crypt' => 'second',
            'type' => 'digital_document',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'display_order' => 2,
            'creator_id' => $user->id,
        ]);

        $ordered = Attachment::orderedByDisplay()->get();

        $this->assertEquals('First', $ordered[0]->name);
        $this->assertEquals('Second', $ordered[1]->name);
        $this->assertEquals('Third', $ordered[2]->name);
    }

    public function test_attachment_types_constants_exist(): void
    {
        $this->assertEquals('mail', Attachment::TYPE_MAIL);
        $this->assertEquals('record', Attachment::TYPE_RECORD);
        $this->assertEquals('digital_folder', Attachment::TYPE_DIGITAL_FOLDER);
        $this->assertEquals('digital_document', Attachment::TYPE_DIGITAL_DOCUMENT);
        $this->assertEquals('artifact', Attachment::TYPE_ARTIFACT);
        $this->assertEquals('book', Attachment::TYPE_BOOK);
        $this->assertEquals('periodic', Attachment::TYPE_PERIODIC);
    }

    public function test_can_store_file_hash(): void
    {
        $user = User::factory()->create();

        $fileHash = md5('test_content');

        $attachment = Attachment::create([
            'name' => 'Hashed File.pdf',
            'path' => 'docs/',
            'crypt' => 'hash123',
            'type' => 'digital_document',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'file_hash_md5' => $fileHash,
            'creator_id' => $user->id,
        ]);

        $this->assertEquals($fileHash, $attachment->file_hash_md5);
        $this->assertEquals(32, strlen($attachment->file_hash_md5));
    }
}
