<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Record;
use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AttachmentApiController extends Controller
{
    /**
     * Retourne l'état de santé de l'API des attachments
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'message' => 'Attachment API is running'
        ]);
    }

    /**
     * Liste tous les attachments disponibles
     */
    public function index(): JsonResponse
    {
        try {
            $attachments = Attachment::with(['attachable'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'name' => $attachment->name,
                        'mime_type' => $attachment->type,
                        'size' => $attachment->size,
                        'path' => $attachment->path,
                        'attachable_type' => $attachment->attachable_type,
                        'attachable_id' => $attachment->attachable_id,
                        'created_at' => $attachment->created_at,
                        'updated_at' => $attachment->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $attachments,
                'count' => $attachments->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching attachments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attachments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extrait le contenu d'un attachment (stub pour compatibilité MCP)
     */
    public function extractContent(Request $request): JsonResponse
    {
        $request->validate([
            'attachment_id' => 'required|exists:attachments,id',
        ]);

        try {
            $attachment = Attachment::findOrFail($request->attachment_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'attachment_id' => $attachment->id,
                    'name' => $attachment->name,
                    'mime_type' => $attachment->type,
                    'content' => 'Content extraction will be handled by MCP server',
                    'message' => 'Use MCP server for actual content extraction'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing attachment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing attachment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retourne les métadonnées de tous les attachments
     */
    public function metadata(): JsonResponse
    {
        try {
            $attachments = Attachment::with(['attachable'])
                ->get()
                ->map(function ($attachment) {
                    $fullPath = Storage::path($attachment->path);

                    return [
                        'id' => $attachment->id,
                        'name' => $attachment->name,
                        'mime_type' => $attachment->type,
                        'size' => $attachment->size,
                        'path' => $attachment->path,
                        'exists' => file_exists($fullPath),
                        'attachable_type' => $attachment->attachable_type,
                        'attachable_id' => $attachment->attachable_id,
                        'created_at' => $attachment->created_at,
                        'updated_at' => $attachment->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $attachments,
                'count' => $attachments->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching attachment metadata: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attachment metadata',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retourne les métadonnées d'un attachment spécifique
     */
    public function singleMetadata($id): JsonResponse
    {
        try {
            $attachment = Attachment::with(['attachable'])->findOrFail($id);
            $fullPath = Storage::path($attachment->path);

            $metadata = [
                'id' => $attachment->id,
                'name' => $attachment->name,
                'mime_type' => $attachment->type,
                'size' => $attachment->size,
                'path' => $attachment->path,
                'exists' => file_exists($fullPath),
                'attachable_type' => $attachment->attachable_type,
                'attachable_id' => $attachment->attachable_id,
                'created_at' => $attachment->created_at,
                'updated_at' => $attachment->updated_at,
            ];

            if (file_exists($fullPath)) {
                $metadata['file_size'] = filesize($fullPath);
                $metadata['last_modified'] = date('Y-m-d H:i:s', filemtime($fullPath));
            }

            return response()->json([
                'success' => true,
                'data' => $metadata
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching single attachment metadata: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attachment metadata',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload un fichier et crée un attachment temporaire
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,txt,docx,doc,rtf,odt',
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            // Stocker le fichier
            $path = $file->store('attachments', 'local');

            // Créer l'attachment
            $attachment = Attachment::create([
                'name' => $originalName,
                'path' => $path,
                'type' => 'mail', // Type générique pour API
                'mime_type' => $mimeType,
                'size' => $size,
                'creator_id' => auth()->id(),
                'crypt' => md5_file($file->getRealPath()),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'attachment' => [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'mime_type' => $attachment->type,
                    'size' => $attachment->size,
                    'path' => $attachment->path,
                    'created_at' => $attachment->created_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
