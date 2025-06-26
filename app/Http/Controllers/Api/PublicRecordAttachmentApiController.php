<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicRecordAttachment;
use App\Models\PublicRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Public Record Attachments
 * Handles file attachments for public records
 */
class PublicRecordAttachmentApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PublicRecordAttachment::with(['publicRecord', 'uploader']);

        // Filter by public record if specified
        if ($request->has('public_record_id')) {
            $query->where('public_record_id', $request->public_record_id);
        }

        // Filter by file type if specified
        if ($request->has('mime_type')) {
            $query->where('mime_type', 'like', '%' . $request->mime_type . '%');
        }

        // Search by original filename
        if ($request->has('search')) {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }

        $attachments = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $attachments->items(),
            'pagination' => [
                'current_page' => $attachments->currentPage(),
                'per_page' => $attachments->perPage(),
                'total' => $attachments->total(),
                'last_page' => $attachments->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicRecordAttachment $attachment): JsonResponse
    {
        $attachment->load(['publicRecord', 'uploader']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attachment->id,
                'public_record_id' => $attachment->public_record_id,
                'file_path' => $attachment->file_path,
                'original_name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
                'size_formatted' => $this->formatFileSize($attachment->size),
                'uploaded_by' => $attachment->uploaded_by,
                'uploader' => $attachment->uploader ? [
                    'id' => $attachment->uploader->id,
                    'name' => $attachment->uploader->name,
                ] : null,
                'public_record' => $attachment->publicRecord ? [
                    'id' => $attachment->publicRecord->id,
                    'title' => $attachment->publicRecord->title,
                    'reference' => $attachment->publicRecord->reference,
                ] : null,
                'created_at' => $attachment->created_at,
                'updated_at' => $attachment->updated_at,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'public_record_id' => 'required|exists:public_records,id',
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $publicRecord = PublicRecord::findOrFail($request->public_record_id);

            // Store the file
            $filePath = $file->store('public_record_attachments', 'public');

            $attachment = PublicRecordAttachment::create([
                'public_record_id' => $publicRecord->id,
                'file_path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);

            $attachment->load(['publicRecord', 'uploader']);

            return response()->json([
                'success' => true,
                'message' => 'Pièce jointe ajoutée avec succès',
                'data' => $attachment,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la pièce jointe',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicRecordAttachment $attachment): JsonResponse
    {
        try {
            // Delete the file from storage
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $attachment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pièce jointe supprimée avec succès',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la pièce jointe',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download the attachment file.
     */
    public function download(PublicRecordAttachment $attachment): BinaryFileResponse|JsonResponse
    {
        try {
            $filePath = storage_path('app/public/' . $attachment->file_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé',
                ], 404);
            }

            return response()->download($filePath, $attachment->original_name);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get attachments by public record.
     */
    public function byPublicRecord(PublicRecord $publicRecord): JsonResponse
    {
        $attachments = $publicRecord->attachments()
            ->with(['uploader'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->size,
                    'size_formatted' => $this->formatFileSize($attachment->size),
                    'uploader' => $attachment->uploader ? [
                        'id' => $attachment->uploader->id,
                        'name' => $attachment->uploader->name,
                    ] : null,
                    'created_at' => $attachment->created_at,
                ];
            }),
        ]);
    }

    /**
     * Format file size in human readable format.
     */
    private function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / (1024 ** $power), 2) . ' ' . $units[$power];
    }
}
