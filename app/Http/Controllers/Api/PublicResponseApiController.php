<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicResponse;
use App\Models\PublicDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for Public Responses
 * Handles responses to document requests for the public portal
 */
class PublicResponseApiController extends Controller
{
    // Message constants
    private const RESPONSE_CREATED = 'Response created successfully';
    private const RESPONSE_UPDATED = 'Response updated successfully';
    private const RESPONSE_DELETED = 'Response deleted successfully';
    private const RESPONSE_NOT_FOUND = 'Response not found';
    private const ACCESS_DENIED = 'Access denied';

    // Validation rule constants
    private const REQUIRED_STRING = 'required|string';

    private const STORE_RULES = [
        'document_request_id' => 'required|exists:public_document_requests,id',
        'content' => self::REQUIRED_STRING,
        'status' => 'required|in:draft,sent',
    ];

    private const UPDATE_RULES = [
        'content' => 'sometimes|required|string',
        'status' => 'sometimes|required|in:draft,sent',
    ];

    /**
     * Get responses for a document request
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'document_request_id' => 'required|exists:public_document_requests,id',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $documentRequestId = $request->get('document_request_id');

        // Check if user can access this document request
        $documentRequest = PublicDocumentRequest::find($documentRequestId);
        $user = $request->user();

        if ($user && $documentRequest->user_id !== $user->id) {
            return $this->errorResponse(self::ACCESS_DENIED, 403);
        }

        $query = PublicResponse::with(['user', 'documentRequest', 'attachments'])
            ->where('document_request_id', $documentRequestId);

        $query->orderBy('created_at', 'desc');

        $perPage = min($request->get('per_page', 10), 50);
        $responses = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($responses->items())->map(function ($response) {
                return $this->transformResponse($response);
            })->toArray(),
            'pagination' => [
                'current_page' => $responses->currentPage(),
                'last_page' => $responses->lastPage(),
                'per_page' => $responses->perPage(),
                'total' => $responses->total(),
            ]
        ]);
    }

    /**
     * Get single response
     */
    public function show(Request $request, $id): JsonResponse
    {
        $response = PublicResponse::with(['user', 'documentRequest', 'attachments'])->find($id);

        if (!$response) {
            return $this->errorResponse(self::RESPONSE_NOT_FOUND, 404);
        }

        // Check access rights
        $user = $request->user();
        if ($user && $response->documentRequest->user_id !== $user->id) {
            return $this->errorResponse(self::ACCESS_DENIED, 403);
        }

        return $this->successResponse('Response retrieved successfully', $this->transformResponse($response));
    }

    /**
     * Store new response (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(self::STORE_RULES);

        // Set user (admin responding)
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        // Set sent_at if status is sent
        if ($validated['status'] === 'sent') {
            $validated['sent_at'] = now();
        }

        $response = PublicResponse::create($validated);
        $response->load(['user', 'documentRequest', 'attachments']);

        return $this->successResponse(
            self::RESPONSE_CREATED,
            $this->transformResponse($response),
            201
        );
    }

    /**
     * Update response (admin only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $response = PublicResponse::find($id);

        if (!$response) {
            return $this->errorResponse(self::RESPONSE_NOT_FOUND, 404);
        }

        $validated = $request->validate(self::UPDATE_RULES);

        // Update sent_at if status changes to sent
        if (isset($validated['status']) && $validated['status'] === 'sent' && !$response->sent_at) {
            $validated['sent_at'] = now();
        }

        $response->update($validated);
        $response->load(['user', 'documentRequest', 'attachments']);

        return $this->successResponse(
            self::RESPONSE_UPDATED,
            $this->transformResponse($response->fresh())
        );
    }

    /**
     * Delete response (admin only)
     */
    public function destroy($id): JsonResponse
    {
        $response = PublicResponse::find($id);

        if (!$response) {
            return $this->errorResponse(self::RESPONSE_NOT_FOUND, 404);
        }

        $response->delete();

        return $this->successResponse(self::RESPONSE_DELETED);
    }

    /**
     * Mark response as sent
     */
    public function markAsSent($id): JsonResponse
    {
        $response = PublicResponse::find($id);

        if (!$response) {
            return $this->errorResponse(self::RESPONSE_NOT_FOUND, 404);
        }

        $response->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response->load(['user', 'documentRequest', 'attachments']);

        return $this->successResponse(
            'Response marked as sent',
            $this->transformResponse($response)
        );
    }

    /**
     * Get responses by document request (public access for request owner)
     */
    public function byDocumentRequest(Request $request, $documentRequestId): JsonResponse
    {
        $documentRequest = PublicDocumentRequest::find($documentRequestId);

        if (!$documentRequest) {
            return $this->errorResponse('Document request not found', 404);
        }

        // Check access rights
        $user = $request->user();
        if ($user && $documentRequest->user_id !== $user->id) {
            return $this->errorResponse(self::ACCESS_DENIED, 403);
        }

        $responses = PublicResponse::with(['user', 'attachments'])
            ->where('document_request_id', $documentRequestId)
            ->where('status', 'sent') // Only show sent responses to users
            ->orderBy('sent_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $responses->map(function ($response) {
                return $this->transformResponse($response, true); // Public view
            })
        ]);
    }

    /**
     * Transform response data for API response
     */
    private function transformResponse($response, bool $publicView = false): array
    {
        $data = [
            'id' => $response->id,
            'content' => $response->content,
            'status' => $response->status,
            'status_label' => $this->getStatusLabel($response->status),
            'sent_at' => $response->sent_at?->toISOString(),
            'formatted_sent_at' => $response->sent_at ?
                $response->sent_at->format('d/m/Y H:i') : null,
            'created_at' => $response->created_at?->toISOString(),
            'updated_at' => $response->updated_at?->toISOString(),
        ];

        // Add admin info for admin view
        if (!$publicView) {
            $data['document_request_id'] = $response->document_request_id;
            $data['user'] = $response->user ? [
                'id' => $response->user->id,
                'name' => $response->user->name,
                'email' => $response->user->email,
            ] : null;
            $data['document_request'] = $response->documentRequest ? [
                'id' => $response->documentRequest->id,
                'title' => $response->documentRequest->title,
                'status' => $response->documentRequest->status,
            ] : null;
        } else {
            // Public view - hide sensitive data
            $data['admin_name'] = $response->user ? $response->user->name : 'Administrator';
        }

        // Add attachments if available
        if ($response->attachments) {
            $data['attachments'] = $response->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'filename' => $attachment->filename,
                    'file_size' => $attachment->file_size,
                    'file_type' => $attachment->file_type,
                ];
            });
        } else {
            $data['attachments'] = [];
        }

        return $data;
    }

    /**
     * Get status label
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'sent' => 'Envoyé',
            default => 'Statut inconnu'
        };
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
