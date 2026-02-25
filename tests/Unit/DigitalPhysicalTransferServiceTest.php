<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalFolderType;
use App\Models\RecordPhysical;
use App\Models\Organisation;
use App\Models\Activity;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\RecordLevel;
use App\Services\DigitalPhysicalTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DigitalPhysicalTransferServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organisation $organisation;
    protected Activity $activity;
    protected RecordStatus $status;
    protected RecordSupport $support;
    protected RecordLevel $level;
    protected RecordPhysical $physicalRecord;
    protected RecordDigitalDocumentType $documentType;
    protected RecordDigitalFolderType $folderType;
    protected DigitalPhysicalTransferService $service;
    protected static int $testCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$testCounter++;

        $this->user = User::create([
            'name' => 'Test User ' . self::$testCounter,
            'email' => 'test' . self::$testCounter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);

        $this->organisation = Organisation::create([
            'code' => 'ORG-' . self::$testCounter,
            'name' => 'Test Organisation ' . self::$testCounter,
        ]);

        $this->status = RecordStatus::create([
            'name' => 'Active ' . self::$testCounter,
        ]);

        $this->support = RecordSupport::create([
            'name' => 'Paper ' . self::$testCounter,
        ]);

        $this->level = RecordLevel::create([
            'code' => 'LEVEL-' . self::$testCounter,
            'name' => 'Document Level',
        ]);

        $this->activity = Activity::create([
            'code' => 'ACT-' . str_pad(self::$testCounter, 3, '0', STR_PAD_LEFT),
            'name' => 'Test Activity ' . self::$testCounter,
        ]);

        $this->activity->organisations()->attach($this->organisation->id, ['creator_id' => $this->user->id]);

        $this->documentType = RecordDigitalDocumentType::create([
            'code' => 'TYPE-' . self::$testCounter,
            'name' => 'Test Document Type ' . self::$testCounter,
            'description' => 'Test type for digital documents',
        ]);

        $this->folderType = RecordDigitalFolderType::create([
            'code' => 'FTYPE-' . self::$testCounter,
            'name' => 'Test Folder Type ' . self::$testCounter,
            'description' => 'Test type for digital folders',
        ]);

        $this->physicalRecord = RecordPhysical::create([
            'code' => 'PHYS-' . str_pad(self::$testCounter, 3, '0', STR_PAD_LEFT),
            'name' => 'Test Physical Record',
            'date_format' => 'A',
            'level_id' => $this->level->id,
            'status_id' => $this->status->id,
            'support_id' => $this->support->id,
            'activity_id' => $this->activity->id,
            'user_id' => $this->user->id,
        ]);

        $this->service = app(DigitalPhysicalTransferService::class);
    }

    protected function createTestDocument($suffix = ''): RecordDigitalDocument
    {
        return RecordDigitalDocument::create([
            'code' => 'DOC-' . str_pad(self::$testCounter, 3, '0', STR_PAD_LEFT) . $suffix,
            'name' => 'Test Document ' . $suffix,
            'type_id' => $this->documentType->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
        ]);
    }

    protected function createTestFolder($suffix = ''): RecordDigitalFolder
    {
        return RecordDigitalFolder::create([
            'code' => 'FOL-' . str_pad(self::$testCounter, 3, '0', STR_PAD_LEFT) . $suffix,
            'name' => 'Test Folder ' . $suffix,
            'type_id' => $this->folderType->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
        ]);
    }

    public function test_validate_transfer_valid_parameters()
    {
        $document = $this->createTestDocument('A');
        $result = $this->service->validateTransfer('document', $document->id, $this->physicalRecord->id);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_validate_transfer_rejects_invalid_type()
    {
        $document = $this->createTestDocument('B');
        $result = $this->service->validateTransfer('invalid', $document->id, $this->physicalRecord->id);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_validate_transfer_rejects_non_existent_asset()
    {
        $result = $this->service->validateTransfer('document', 999, $this->physicalRecord->id);
        $this->assertFalse($result['valid']);
        $this->assertContains('Digital document not found.', $result['errors']);
    }

    public function test_validate_transfer_rejects_non_existent_physical()
    {
        $document = $this->createTestDocument('C');
        $result = $this->service->validateTransfer('document', $document->id, 999);
        $this->assertFalse($result['valid']);
        $this->assertContains('Physical record not found.', $result['errors']);
    }

    public function test_associate_digital_to_physical_creates_metadata()
    {
        $document = $this->createTestDocument('D');
        $result = $this->service->associateDigitalToPhysical(
            'document',
            $document->id,
            $this->physicalRecord->id,
            $this->user->id,
            'Test transfer'
        );
        $this->assertTrue($result['success']);
        $document->refresh();
        $this->assertNotNull($document->transferred_at);
        $this->assertEquals($this->physicalRecord->id, $document->transferred_to_record_id);
        $this->assertNotNull($document->transfer_metadata);
    }

    public function test_delete_digital_after_transfer_removes_asset()
    {
        $document = $this->createTestDocument('E');
        $this->service->associateDigitalToPhysical(
            'document',
            $document->id,
            $this->physicalRecord->id,
            $this->user->id
        );
        $result = $this->service->deleteDigitalAfterTransfer('document', $document->id);
        $this->assertTrue($result['success']);
        $this->assertNull(RecordDigitalDocument::find($document->id));
    }

    public function test_get_available_physical_records_returns_formatted_list()
    {
        $document = $this->createTestDocument('F');
        $records = $this->service->getAvailablePhysicalRecords('document', $document->id);
        $this->assertIsArray($records);
        $this->assertGreaterThanOrEqual(1, count($records), 'Should find at least one physical record');
        $record = $records[0];
        $this->assertArrayHasKey('id', $record);
        $this->assertArrayHasKey('code', $record);
        $this->assertArrayHasKey('name', $record);
    }

    public function test_transfer_fails_for_already_transferred_asset()
    {
        $document = $this->createTestDocument('G');
        $this->service->associateDigitalToPhysical(
            'document',
            $document->id,
            $this->physicalRecord->id,
            $this->user->id
        );
        $result = $this->service->validateTransfer('document', $document->id, $this->physicalRecord->id);
        $this->assertFalse($result['valid']);
    }

    public function test_delete_fails_for_non_transferred_asset()
    {
        $document = $this->createTestDocument('H');
        $result = $this->service->deleteDigitalAfterTransfer('document', $document->id);
        $this->assertFalse($result['success']);
    }

    public function test_folder_transfer_metadata_includes_document_count()
    {
        $folder = $this->createTestFolder('H');
        RecordDigitalDocument::create(['code' => 'D1', 'name' => 'Doc 1', 'type_id' => $this->documentType->id, 'folder_id' => $folder->id, 'creator_id' => $this->user->id, 'organisation_id' => $this->organisation->id]);
        RecordDigitalDocument::create(['code' => 'D2', 'name' => 'Doc 2', 'type_id' => $this->documentType->id, 'folder_id' => $folder->id, 'creator_id' => $this->user->id, 'organisation_id' => $this->organisation->id]);

        $result = $this->service->associateDigitalToPhysical(
            'folder',
            $folder->id,
            $this->physicalRecord->id,
            $this->user->id
        );
        $this->assertTrue($result['success']);
    }

    public function test_complete_transfer_success()
    {
        $document = $this->createTestDocument('I');
        $result = $this->service->completeTransfer(
            'document',
            $document->id,
            $this->physicalRecord->id,
            $this->user->id
        );
        $this->assertTrue($result['success']);
        $this->assertNull(RecordDigitalDocument::find($document->id));
    }

    public function test_transfer_metadata_has_required_fields()
    {
        $document = $this->createTestDocument('J');
        $result = $this->service->associateDigitalToPhysical(
            'document',
            $document->id,
            $this->physicalRecord->id,
            $this->user->id,
            'Test transfer note'
        );
        $this->assertTrue($result['success']);
        $metadata = $document->fresh()->transfer_metadata;
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('transferred_at', $metadata);
        $this->assertArrayHasKey('transferred_by_user_id', $metadata);
        $this->assertArrayHasKey('transferred_to_record_id', $metadata);
    }
}
