<?php

namespace Tests\Feature;

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

class DigitalPhysicalTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organisation $organisation;
    protected Activity $activity;
    protected RecordStatus $status;
    protected RecordSupport $support;
    protected RecordLevel $level;
    protected RecordPhysical $physicalRecord;
    protected RecordDigitalDocument $digitalDocument;
    protected RecordDigitalFolder $digitalFolder;
    protected RecordDigitalDocumentType $documentType;
    protected RecordDigitalFolderType $folderType;
    protected DigitalPhysicalTransferService $transferService;
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
            'description' => 'Active status',
        ]);

        $this->support = RecordSupport::create([
            'name' => 'Paper ' . self::$testCounter,
            'description' => 'Paper support',
        ]);

        $this->level = RecordLevel::create([
            'code' => 'LEVEL-' . self::$testCounter,
            'name' => 'Document Level',
            'description' => 'Document level',
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

        $this->digitalDocument = RecordDigitalDocument::create([
            'code' => 'DOC-' . str_pad(self::$testCounter, 3, '0', STR_PAD_LEFT) . 'A',
            'name' => 'Test Digital Document',
            'type_id' => $this->documentType->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
        ]);

        $this->digitalFolder = RecordDigitalFolder::create([
            'code' => 'FOL-' . str_pad(self::$testCounter, 3, '0', STR_PAD_LEFT) . 'A',
            'name' => 'Test Digital Folder',
            'type_id' => $this->folderType->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
        ]);

        $this->transferService = app(DigitalPhysicalTransferService::class);
    }

    public function test_transfer_form_endpoint_returns_physical_records()
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/record-digital-transfer/form?type=document&id=' . $this->digitalDocument->id);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'type',
                'digital_id',
                'digital_name',
                'physical_records' => [
                    '*' => ['id', 'code', 'name'],
                ],
            ],
        ]);
    }

    public function test_transfer_form_returns_404_for_non_existent_document()
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/record-digital-transfer/form?type=document&id=999');

        $response->assertNotFound();
    }

    public function test_document_transfer_success()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/record-digital-transfer/', [
            'type' => 'document',
            'digital_id' => $this->digitalDocument->id,
            'physical_id' => $this->physicalRecord->id,
            'notes' => 'Test transfer',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertNull(RecordDigitalDocument::find($this->digitalDocument->id));
    }

    public function test_folder_transfer_with_contents()
    {
        // Add documents to folder
        RecordDigitalDocument::create([
            'code' => 'DOC-' . str_pad(self::$testCounter, 3, '0', STR_PAD_LEFT) . 'B',
            'name' => 'Document in folder',
            'folder_id' => $this->digitalFolder->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/record-digital-transfer/', [
            'type' => 'folder',
            'digital_id' => $this->digitalFolder->id,
            'physical_id' => $this->physicalRecord->id,
            'notes' => 'Transfer folder with contents',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertNull(RecordDigitalFolder::find($this->digitalFolder->id));
    }

    public function test_validation_fails_for_non_existent_physical_record()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/record-digital-transfer/', [
            'type' => 'document',
            'digital_id' => $this->digitalDocument->id,
            'physical_id' => 999,
            'notes' => 'Test',
        ]);

        $response->assertStatus(422);
    }

    public function test_validation_fails_for_already_transferred_document()
    {
        // First transfer
        $this->transferService->associateDigitalToPhysical(
            'document',
            $this->digitalDocument->id,
            $this->physicalRecord->id,
            $this->user->id
        );

        // Try to transfer again
        $response = $this->actingAs($this->user)->postJson('/api/v1/record-digital-transfer/', [
            'type' => 'document',
            'digital_id' => $this->digitalDocument->id,
            'physical_id' => $this->physicalRecord->id,
            'notes' => 'Test',
        ]);

        $response->assertStatus(422);
    }

    public function test_transfer_api_endpoint()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/record-digital-transfer/', [
            'type' => 'document',
            'digital_id' => $this->digitalDocument->id,
            'physical_id' => $this->physicalRecord->id,
            'notes' => 'Transfer test',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'message']);
    }

    public function test_transfer_requires_authentication()
    {
        $response = $this->postJson('/api/v1/record-digital-transfer/', [
            'type' => 'document',
            'digital_id' => $this->digitalDocument->id,
            'physical_id' => $this->physicalRecord->id,
            'notes' => 'Test',
        ]);

        $response->assertUnauthorized();
    }

    public function test_cancel_transfer_operation()
    {
        $this->transferService->associateDigitalToPhysical(
            'document',
            $this->digitalDocument->id,
            $this->physicalRecord->id,
            $this->user->id
        );

        $response = $this->actingAs($this->user)->deleteJson(
            '/api/v1/record-digital-transfer/cancel',
            [
                'type' => 'document',
                'digital_id' => $this->digitalDocument->id,
            ]
        );

        $response->assertOk();
        $this->assertNotNull(RecordDigitalDocument::find($this->digitalDocument->id));
    }

    public function test_transfer_metadata_is_stored_correctly()
    {
        $this->transferService->associateDigitalToPhysical(
            'document',
            $this->digitalDocument->id,
            $this->physicalRecord->id,
            $this->user->id,
            'Test notes'
        );

        $document = RecordDigitalDocument::find($this->digitalDocument->id);
        $this->assertNotNull($document->transfer_metadata);

        $metadata = json_decode($document->transfer_metadata, true);
        $this->assertEquals('document', $metadata['transferred_from_type']);
        $this->assertEquals($this->digitalDocument->id, $metadata['transferred_from_id']);
    }

    public function test_physical_record_linked_metadata_updated()
    {
        $this->transferService->associateDigitalToPhysical(
            'document',
            $this->digitalDocument->id,
            $this->physicalRecord->id,
            $this->user->id
        );

        $physicalRecord = RecordPhysical::find($this->physicalRecord->id);
        $linkedMetadata = json_decode($physicalRecord->linked_digital_metadata ?? '[]', true);

        $this->assertNotEmpty($linkedMetadata);
    }
}
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('document', $response->json('data.type'));
    }

    /**
     * Test transfer form endpoint for non-existent document
     */
    public function test_transfer_form_returns_404_for_non_existent_document()
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/record-digital-transfer/form', [
            'type' => 'document',
            'id' => 999,
        ]);

        $response->assertNotFound();
    }

    /**
     * Test successful document transfer
     */
    public function test_document_transfer_success()
    {
        $result = $this->transferService->completeTransfer(
            'document',
            $this->digitalDocument->id,
            $this->physicalRecord->id,
            $this->user->id,
            'Test transfer'
        );

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('transfer_metadata', $result);

        // Verify digital document is deleted
        $this->assertNull(RecordDigitalDocument::find($this->digitalDocument->id));

        // Verify physical record has transfer metadata
        $physicalRecord = RecordPhysical::find($this->physicalRecord->id);
        $this->assertNotNull($physicalRecord->linked_digital_metadata);
    }

    /**
     * Test folder transfer with all its contents
     */
    public function test_folder_transfer_with_contents()
    {
        // Add documents to folder
        RecordDigitalDocument::create([
            'code' => 'DIG-DOC-002',
            'name' => 'Test Doc 2',
            'folder_id' => $this->digitalFolder->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
        ]);
        RecordDigitalDocument::create([
            'code' => 'DIG-DOC-003',
            'name' => 'Test Doc 3',
            'folder_id' => $this->digitalFolder->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
        ]);
        RecordDigitalDocument::create([
            'code' => 'DIG-DOC-004',
            'name' => 'Test Doc 4',
            'folder_id' => $this->digitalFolder->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
        ]);

        $result = $this->transferService->completeTransfer(
            'folder',
            $this->digitalFolder->id,
            $this->physicalRecord->id,
            $this->user->id
        );

        $this->assertTrue($result['success']);

        // Verify folder is deleted
        $this->assertNull(RecordDigitalFolder::find($this->digitalFolder->id));

        // Verify all documents in folder are deleted
        $this->assertEquals(0, RecordDigitalDocument::where('folder_id', $this->digitalFolder->id)->count());
    }

    /**
     * Test validation fails for non-existent physical record
     */
    public function test_validation_fails_for_non_existent_physical_record()
    {
        $validation = $this->transferService->validateTransfer(
            'document',
            $this->digitalDocument->id,
            999
        );

        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
    }

    /**
     * Test validation fails for already transferred document
    */
    public function test_validation_fails_for_already_transferred_document()
    {
        // First transfer
        $this->transferService->associateDigitalToPhysical(
            'document',
            $this->digitalDocument->id,
            $this->physicalRecord->id,
            $this->user->id
        );

        // Try to transfer again
        $validation = $this->transferService->validateTransfer(
            'document',
            $this->digitalDocument->id,
            $this->physicalRecord->id
        );

        $this->assertFalse($validation['valid']);
    }

    /**
     * Test transfer API endpoint
     */
    public function test_transfer_api_endpoint()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/record-digital-transfer/', [
            'type' => 'document',
            'digital_id' => $this->digitalDocument->id,
            'physical_id' => $this->physicalRecord->id,
            'notes' => 'API test transfer',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));
    }

    /**
     * Test transfer endpoint without authorization
     */
    public function test_transfer_requires_authentication()
    {
        $response = $this->postJson('/api/v1/record-digital-transfer/', [
            'type' => 'document',
            'digital_id' => $this->digitalDocument->id,
            'physical_id' => $this->physicalRecord->id,
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test cancel transfer operation
     */
    public function test_cancel_transfer_operation()
    {
        // First transfer
        $this->transferService->associateDigitalToPhysical(
            'document',
            $this->digitalDocument->id,
            $this->physicalRecord->id,
            $this->user->id
        );

        // Verify transfer was recorded
        $digitalAsset = RecordDigitalDocument::withTrashed()->find($this->digitalDocument->id);
        $this->assertNotNull($digitalAsset->transferred_at);

        // Now cancel it
        $response = $this->actingAs($this->user)->deleteJson('/api/v1/record-digital-transfer/cancel', [
            'type' => 'document',
            'id' => $this->digitalDocument->id,
        ]);

        $response->assertOk();

        // Verify transfer fields are reset
        $digitalAsset->refresh();
        $this->assertNull($digitalAsset->transferred_at);
        $this->assertNull($digitalAsset->transferred_to_record_id);
    }

    /**
     * Test transfer metadata is stored correctly
     */
    public function test_transfer_metadata_is_stored()
    {
        $result = $this->transferService->associateDigitalToPhysical(
            'document',
            $this->digitalDocument->id,
            $this->physicalRecord->id,
            $this->user->id,
            'Test notes'
        );

        $this->assertTrue($result['success']);

        $digitalAsset = RecordDigitalDocument::withTrashed()->find($this->digitalDocument->id);
        $metadata = $digitalAsset->transfer_metadata;

        $this->assertArrayHasKey('transferred_at', $metadata);
        $this->assertArrayHasKey('transferred_by_user_id', $metadata);
        $this->assertArrayHasKey('transferred_to_record_id', $metadata);
        $this->assertEquals($this->user->id, $metadata['transferred_by_user_id']);
        $this->assertEquals('Test notes', $metadata['notes']);
    }

    /**
     * Test physical record linked digital metadata is updated
     */
    public function test_physical_record_linked_metadata_updated()
    {
        $this->transferService->associateDigitalToPhysical(
            'document',
            $this->digitalDocument->id,
            $this->physicalRecord->id,
            $this->user->id
        );

        $physicalRecord = RecordPhysical::find($this->physicalRecord->id);
        $linkedMetadata = $physicalRecord->linked_digital_metadata;

        $this->assertArrayHasKey('linked_digital_assets', $linkedMetadata);
        $this->assertCount(1, $linkedMetadata['linked_digital_assets']);
        $this->assertEquals('document', $linkedMetadata['linked_digital_assets'][0]['type']);
        $this->assertEquals($this->digitalDocument->id, $linkedMetadata['linked_digital_assets'][0]['digital_id']);
    }
}
