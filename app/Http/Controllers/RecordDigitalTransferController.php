<?php

namespace App\Http\Controllers;

use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalFolder;
use App\Models\RecordPhysical;
use App\Services\DigitalPhysicalTransferService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecordDigitalTransferController extends Controller
{
    public function __construct(private DigitalPhysicalTransferService $transferService)
    {
    }

    /**
     * Show transfer form data including available physical records
     */
    public function showTransferForm(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:document,folder',
            'id' => 'required|integer',
        ]);

        $type = $request->get('type');
        $digitalId = $request->get('id');

        // Check authorization
        $digitalAsset = $this->getDigitalAsset($type, $digitalId);
        if (!$digitalAsset) {
            return response()->json([
                'success' => false,
                'message' => 'Digital ' . $type . ' not found',
            ], 404);
        }

        $this->authorize('view', $digitalAsset);

        // Get available physical records
        $physicalRecords = $this->transferService->getAvailablePhysicalRecords($type, $digitalId);

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $type,
                'digital_id' => $digitalId,
                'digital_name' => $digitalAsset->name,
                'digital_code' => $digitalAsset->code ?? null,
                'physical_records' => $physicalRecords,
            ],
        ]);
    }

    /**
     * Process transfer request
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:document,folder',
            'digital_id' => 'required|integer',
            'physical_id' => 'required|integer',
            'notes' => 'nullable|string|max:500',
        ]);

        $type = $request->get('type');
        $digitalId = $request->get('digital_id');
        $physicalId = $request->get('physical_id');
        $notes = $request->get('notes', '');

        // Check authorization
        $digitalAsset = $this->getDigitalAsset($type, $digitalId);
        if (!$digitalAsset) {
            return response()->json([
                'success' => false,
                'message' => 'Digital ' . $type . ' not found',
            ], 404);
        }

        $this->authorize('delete', $digitalAsset);

        $physicalRecord = RecordPhysical::find($physicalId);
        if (!$physicalRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Physical record not found',
                'errors' => ['Physical record not found'],
            ], 422);
        }

        $this->authorize('view', $physicalRecord);

        // Process complete transfer
        $result = $this->transferService->completeTransfer(
            $type,
            $digitalId,
            $physicalId,
            auth()->id(),
            $notes
        );

        if (!$result['success']) {
            $statusCode = isset($result['partial_success']) && $result['partial_success'] ? 207 : 422;
            return response()->json($result, $statusCode);
        }

        return response()->json($result, 200);
    }

    /**
     * Cancel pending transfer
     */
    public function cancel(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:document,folder',
            'id' => 'required|integer',
        ]);

        $type = $request->get('type');
        $digitalId = $request->get('id');

        // Check authorization
        $digitalAsset = $this->getDigitalAsset($type, $digitalId);
        if (!$digitalAsset) {
            return response()->json([
                'success' => false,
                'message' => 'Digital ' . $type . ' not found',
            ], 404);
        }

        $this->authorize('update', $digitalAsset);

        // Check if transfer was initiated
        if (!$digitalAsset->transferred_at) {
            return response()->json([
                'success' => false,
                'message' => 'No active transfer to cancel',
            ], 400);
        }

        try {
            // Reset transfer fields
            $digitalAsset->update([
                'transferred_at' => null,
                'transferred_to_record_id' => null,
                'transfer_metadata' => null,
            ]);

            // Log the cancellation
            // TODO: Re-enable activity logging after fixing Spatie config
            // if (\Auth::check()) {
            //     \Spatie\ActivityLog\Facades\Activity::causedBy(auth()->user())
            //         ->performedOn($digitalAsset)
            //         ->withProperties(['type' => $type])
            //         ->log('digital_transfer_cancelled');
            // }

            return response()->json([
                'success' => true,
                'message' => 'Transfer cancelled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel transfer: ' . $e->getMessage(),
            ], 500);
        }
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
}
