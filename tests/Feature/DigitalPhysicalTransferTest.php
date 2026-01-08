<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Permission;
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

        // Create and assign superadmin role for test access
        $role = \App\Models\Role::firstOrCreate(['name' => 'superadmin']);
        $this->user->roles()->attach($role->id);

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
            'type_id' => $this->documentType->id,
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
                'id' => $this->digitalDocument->id,
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

        $metadata = $document->transfer_metadata;
        $this->assertIsArray($metadata);
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
        $linkedMetadata = $physicalRecord->linked_digital_metadata;

        $this->assertIsArray($linkedMetadata);
        $this->assertNotEmpty($linkedMetadata);
    }
}
