<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicResponseAttachment;
use App\Models\PublicResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Public Response Attachments
 * Handles file attachments for responses to document requests
 */
class PublicResponseAttachmentApiController extends Controller
{
    // Message constants
    private const ATTACHMENT_UPLOADED = 'Attachment uploaded successfully';
    private const ATTACHMENT_DELETED = 'Attachment deleted successfully';
    private const ATTACHMENT_NOT_FOUND = 'Attachment not found';
    private const ACCESS_DENIED = 'Access denied';

    private const UPLOAD_RULES = [
        'response_id' => 'required|exists:public_responses,id',
        'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt,xlsx,xls',
    ];

    /**
     * Get attachments for a response
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'response_id' => 'required|exists:public_responses,id',
        ]);

        $responseId = $request->get('response_id');

        // Check access rights
        $response = PublicResponse::with(['documentRequest'])->find($responseId);
        $user = $request->user();

        if ($user && $response->documentRequest->user_id !== $user->id) {
            return $this->errorResponse(self::ACCESS_DENIED, 403);
        }

        $attachments = PublicResponseAttachment::where('response_id', $responseId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attachments->map(function ($attachment) {
                return $this->transformAttachment($attachment);
            })
        ]);
    }

    /**
     * Upload new attachment (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(self::UPLOAD_RULES);

        $file = $request->file('file');
        $responseId = $validated['response_id'];

        // Store file
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('public/response_attachments', $filename);

        // Create attachment record
        $attachment = PublicResponseAttachment::create([
            'response_id' => $responseId,
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientMimeType(),
            'uploaded_by' => $request->user() ? $request->user()->id : null,
        ]);

        return $this->successResponse(
            self::ATTACHMENT_UPLOADED,
            $this->transformAttachment($attachment),
            201
        );
    }

    /**
     * Download attachment
     */
    public function download(Request $request, $id): BinaryFileResponse|JsonResponse
    {
        $attachment = PublicResponseAttachment::with(['response.documentRequest'])->find($id);

        if (!$attachment) {
            return $this->errorResponse(self::ATTACHMENT_NOT_FOUND, 404);
        }

        // Check access rights
        $user = $request->user();
        if ($user && $attachment->response->documentRequest->user_id !== $user->id) {
            return $this->errorResponse(self::ACCESS_DENIED, 403);
        }

        if (!Storage::exists($attachment->file_path)) {
            return $this->errorResponse('File not found on server', 404);
        }

        return response()->download(
            Storage::path($attachment->file_path),
            $attachment->filename
        );
    }

    /**
     * Delete attachment (admin only)
     */
    public function destroy($id): JsonResponse
    {
        $attachment = PublicResponseAttachment::find($id);

        if (!$attachment) {
            return $this->errorResponse(self::ATTACHMENT_NOT_FOUND, 404);
        }

        // Delete file from storage
        if (Storage::exists($attachment->file_path)) {
            Storage::delete($attachment->file_path);
        }

        // Delete record
        $attachment->delete();

        return $this->successResponse(self::ATTACHMENT_DELETED);
    }

    /**
     * Get attachment info
     */
    public function show(Request $request, $id): JsonResponse
    {
        $attachment = PublicResponseAttachment::with(['response.documentRequest'])->find($id);

        if (!$attachment) {
            return $this->errorResponse(self::ATTACHMENT_NOT_FOUND, 404);
        }

        // Check access rights
        $user = $request->user();
        if ($user && $attachment->response->documentRequest->user_id !== $user->id) {
            return $this->errorResponse(self::ACCESS_DENIED, 403);
        }

        return $this->successResponse(
            'Attachment retrieved successfully',
            $this->transformAttachment($attachment)
        );
    }

    /**
     * Transform attachment data for API response
     */
    private function transformAttachment($attachment): array
    {
        return [
            'id' => $attachment->id,
            'response_id' => $attachment->response_id,
            'filename' => $attachment->filename,
            'file_size' => $attachment->file_size,
            'file_size_human' => $this->formatFileSize($attachment->file_size),
            'file_type' => $attachment->file_type,
            'file_extension' => pathinfo($attachment->filename, PATHINFO_EXTENSION),
            'download_url' => route('api.public.response-attachments.download', $attachment->id),
            'uploaded_by' => $attachment->uploaded_by,
            'created_at' => $attachment->created_at?->toISOString(),
            'formatted_created_at' => $attachment->created_at ?
                $attachment->created_at->format('d/m/Y H:i') : null,
        ];
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Success response helper
     */
    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
