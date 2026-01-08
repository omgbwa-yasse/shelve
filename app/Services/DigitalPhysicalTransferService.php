<?php

namespace App\Services;

use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalFolder;
use App\Models\RecordPhysical;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class DigitalPhysicalTransferService
{
    /**
     * Validate if a digital asset can be transferred
     */
    public function validateTransfer(string $type, int $digitalId, int $physicalId): array
    {
        $errors = [];

        // Validate type
        if (!in_array($type, ['document', 'folder'])) {
            $errors[] = 'Invalid transfer type. Must be "document" or "folder".';
        }

        // Validate physical record exists
        $physicalRecord = RecordPhysical::find($physicalId);
        if (!$physicalRecord) {
            $errors[] = 'Physical record not found.';
        }

        // Validate digital asset exists
        $digitalAsset = $this->getDigitalAsset($type, $digitalId);
        if (!$digitalAsset) {
            $errors[] = 'Digital ' . $type . ' not found.';
        }

        // Check if already transferred
        if ($digitalAsset && $digitalAsset->transferred_at) {
            $errors[] = 'This ' . $type . ' has already been transferred.';
        }

        // Check if asset is marked for deletion
        if ($digitalAsset && $digitalAsset->trashed()) {
            $errors[] = 'Cannot transfer a deleted ' . $type . '.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'physicalRecord' => $physicalRecord ?? null,
            'digitalAsset' => $digitalAsset ?? null,
        ];
    }

    /**
     * Process transfer and associate digital to physical
     */
    public function associateDigitalToPhysical(
        string $type,
        int $digitalId,
        int $physicalId,
        int $userId,
        string $notes = ''
    ): array {
        // Validate first
        $validation = $this->validateTransfer($type, $digitalId, $physicalId);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation['errors'],
            ];
        }

        try {
            return DB::transaction(function () use ($type, $digitalId, $physicalId, $userId, $notes) {
                $digitalAsset = $this->getDigitalAsset($type, $digitalId);
                $physicalRecord = RecordPhysical::find($physicalId);

                // Prepare transfer metadata
                $transferMetadata = [
                    'transferred_at' => now()->toIso8601String(),
                    'transferred_by_user_id' => $userId,
                    'transferred_to_record_id' => $physicalId,
                    'original_digital_id' => $digitalId,
                    'digital_type' => $type,
                    'digital_name' => $digitalAsset->name,
                    'notes' => $notes,
                ];

                // Add document/folder specific metadata
                if ($type === 'document') {
                    $transferMetadata['transferred_files_count'] = 1;
                    $transferMetadata['transferred_size_bytes'] = $digitalAsset->attachment?->size ?? 0;
                } elseif ($type === 'folder') {
                    $transferMetadata['transferred_files_count'] = $digitalAsset->documents()->count();
                    $transferMetadata['transferred_size_bytes'] = $digitalAsset->total_size ?? 0;
                }

                // Update digital asset
                $digitalAsset->update([
                    'transferred_at' => now(),
                    'transferred_to_record_id' => $physicalId,
                    'transfer_metadata' => $transferMetadata,
                ]);

                // Update physical record with digital metadata reference
                $currentMetadata = $physicalRecord->linked_digital_metadata ?? [];
                if (!is_array($currentMetadata)) {
                    $currentMetadata = [];
                }

                $currentMetadata['linked_digital_assets'][] = [
                    'type' => $type,
                    'digital_id' => $digitalId,
                    'digital_name' => $digitalAsset->name,
                    'transferred_at' => now()->toIso8601String(),
                    'transferred_by_user_id' => $userId,
                ];

                $physicalRecord->update([
                    'linked_digital_metadata' => $currentMetadata,
                ]);

                // Log the transfer activity (if user is authenticated)
                if (\Auth::check()) {
                    activity()
                        ->causedBy(\Auth::user())
                        ->performedOn($digitalAsset)
                        ->withProperties([
                            'transferred_to_record_id' => $physicalId,
                            'transfer_type' => $type,
                            'notes' => $notes,
                        ])
                        ->log('digital_transferred_to_physical');
                }

                return [
                    'success' => true,
                    'message' => ucfirst($type) . ' successfully associated with physical record',
                    'transfer_metadata' => $transferMetadata,
                    'digital_asset_id' => $digitalId,
                    'physical_record_id' => $physicalId,
                ];
            });
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Delete digital asset after confirmed transfer
     */
    public function deleteDigitalAfterTransfer(string $type, int $digitalId): array
    {
        try {
            return DB::transaction(function () use ($type, $digitalId) {
                $digitalAsset = $this->getDigitalAsset($type, $digitalId);

                if (!$digitalAsset) {
                    return [
                        'success' => false,
                        'message' => 'Digital ' . $type . ' not found',
                    ];
                }

                // Verify it was transferred
                if (!$digitalAsset->transferred_at) {
                    return [
                        'success' => false,
                        'message' => 'Cannot delete: ' . $type . ' has not been transferred',
                    ];
                }

                // Delete associated attachments if document
                if ($type === 'document' && $digitalAsset->attachment) {
                    $this->deleteAttachmentFiles($digitalAsset->attachment);
                    $digitalAsset->attachment->delete();
                }

                // Delete subfolder structure if folder
                if ($type === 'folder') {
                    $this->deleteFolder($digitalAsset);
                }

                // Force delete the asset
                $digitalAsset->forceDelete();

                // Log the deletion (if user is authenticated)
                if (\Auth::check()) {
                    activity()
                        ->causedBy(\Auth::user())
                        ->withProperties([
                            'type' => $type,
                            'digital_id' => $digitalId,
                        ])
                        ->log('digital_asset_deleted_after_transfer');
                }

                return [
                    'success' => true,
                    'message' => ucfirst($type) . ' successfully deleted after transfer',
                ];
            });
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Deletion failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Complete transfer operation (association + deletion)
     */
    public function completeTransfer(
        string $type,
        int $digitalId,
        int $physicalId,
        int $userId,
        string $notes = ''
    ): array {
        // Step 1: Associate
        $associationResult = $this->associateDigitalToPhysical(
            $type,
            $digitalId,
            $physicalId,
            $userId,
            $notes
        );

        if (!$associationResult['success']) {
            return $associationResult;
        }

        // Step 2: Delete
        $deletionResult = $this->deleteDigitalAfterTransfer($type, $digitalId);

        if (!$deletionResult['success']) {
            return [
                'success' => false,
                'message' => 'Association succeeded but deletion failed: ' . $deletionResult['message'],
                'partial_success' => true,
                'association_metadata' => $associationResult['transfer_metadata'] ?? null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Transfer completed successfully',
            'transfer_metadata' => $associationResult['transfer_metadata'] ?? null,
            'digital_asset_id' => $digitalId,
            'physical_record_id' => $physicalId,
        ];
    }

    /**
     * Get digital asset by type and ID
     */
    private function getDigitalAsset(string $type, int $id)
    {
        if ($type === 'document') {
            return RecordDigitalDocument::withTrashed()->find($id);
        } elseif ($type === 'folder') {
            return RecordDigitalFolder::withTrashed()->find($id);
        }

        return null;
    }

    /**
     * Delete attachment files from storage
     */
    private function deleteAttachmentFiles($attachment): void
    {
        if ($attachment->path && Storage::exists($attachment->path)) {
            Storage::delete($attachment->path);
        }

        // Delete thumbnail if exists
        if ($attachment->thumbnail_path && Storage::exists($attachment->thumbnail_path)) {
            Storage::delete($attachment->thumbnail_path);
        }
    }

    /**
     * Recursively delete folder and its contents
     */
    private function deleteFolder(RecordDigitalFolder $folder): void
    {
        // Delete all documents in folder
        $folder->documents()->forceDelete();

        // Recursively delete subfolders
        foreach ($folder->subfolders()->cursor() as $subfolder) {
            $this->deleteFolder($subfolder);
        }
    }

    /**
     * Get available physical records for transfer dialog
     */
    public function getAvailablePhysicalRecords(string $type, int $digitalId, int $limit = 50)
    {
        $query = RecordPhysical::query()
            ->with(['level', 'status', 'support'])
            ->orderBy('code')
            ->limit($limit);

        // Filter by organisation if the digital asset has one
        $digitalAsset = $this->getDigitalAsset($type, $digitalId);
        if ($digitalAsset && $digitalAsset->organisation_id) {
            // Filter physical records where activity has this organisation
            $query->whereHas('activity.organisations', function ($q) use ($digitalAsset) {
                $q->where('organisations.id', $digitalAsset->organisation_id);
            });
        }

        // Load activity with organisations for mapping
        $query->with(['activity' => function ($q) {
            $q->with('organisations');
        }]);

        return $query->get()->map(function ($record) {
            // Get organisation name from the activity's organisations
            $organisation = '';
            if ($record->activity && $record->activity->organisations && $record->activity->organisations->count() > 0) {
                $organisation = $record->activity->organisations->first()->name;
            }
            return [
                'id' => $record->id,
                'code' => $record->code,
                'name' => $record->name,
                'level' => $record->level?->name ?? 'Unknown',
                'status' => $record->status?->name ?? 'Unknown',
                'organisation' => $organisation,
                'reference' => $record->code . ' - ' . $record->name,
            ];
        })->toArray();
    }
}
