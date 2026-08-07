<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for Public Document Requests
 * Handles document request submission and management for the public portal
 */
class PublicDocumentRequestApiController extends Controller
{
    // Message constants
    private const ACCESS_DENIED = 'Access denied';
    private const REQUEST_SUBMITTED = 'Document request submitted successfully';

    // Validation rule constants
    private const REQUIRED_STRING = 'required|string';
    private const REQUIRED_STRING_MAX_255 = 'required|string|max:255';
    private const NULLABLE_STRING_MAX_20 = 'nullable|string|max:20';

    private const STORE_RULES = [
        'title' => self::REQUIRED_STRING_MAX_255,
        'description' => self::REQUIRED_STRING,
        'document_type' => 'required|string|max:100',
        'urgency_level' => 'required|in:low,medium,high',
        'requested_date' => 'required|date',
        'contact_email' => 'required|email',
        'contact_phone' => self::NULLABLE_STRING_MAX_20,
    ];

    /**
     * API: Store new document request
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(self::STORE_RULES);
        $validated['status'] = 'pending';

        // Associate request with authenticated user if available
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        $documentRequest = PublicDocumentRequest::create($validated);

        return $this->successResponse(
            self::REQUEST_SUBMITTED,
            $this->transformDocumentRequest($documentRequest),
            201
        );
    }

    /**
     * API: Get user's document requests
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $requests = PublicDocumentRequest::with(['responses'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => collect($requests->items())->map(function ($request) {
                return $this->transformDocumentRequest($request);
            })->toArray(),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ]
        ]);
    }

    /**
     * API: Get single document request
     */
    public function show(Request $request, PublicDocumentRequest $documentRequest): JsonResponse
    {
        $user = $request->user();

        // Check if user can access this request
        if ($documentRequest->user_id !== $user->id) {
            return $this->errorResponse(self::ACCESS_DENIED, 403);
        }

        $documentRequest->load(['responses']);

        return $this->successResponse('Document request retrieved successfully',
            $this->transformDocumentRequest($documentRequest)
        );
    }

    /**
     * Transform document request data for API response
     */
    private function transformDocumentRequest($documentRequest): array
    {
        return [
            'id' => $documentRequest->id,
            'title' => $documentRequest->title,
            'description' => $documentRequest->description,
            'document_type' => $documentRequest->document_type,
            'urgency_level' => $documentRequest->urgency_level,
            'urgency_label' => $this->getUrgencyLabel($documentRequest->urgency_level),
            'status' => $documentRequest->status,
            'status_label' => $this->getStatusLabel($documentRequest->status),
            'requested_date' => $documentRequest->requested_date,
            'formatted_requested_date' => $documentRequest->requested_date ?
                \Carbon\Carbon::parse($documentRequest->requested_date)->format('d/m/Y') : null,
            'contact_email' => $documentRequest->contact_email,
            'contact_phone' => $documentRequest->contact_phone,
            'user_id' => $documentRequest->user_id,
            'responses_count' => $documentRequest->responses ? $documentRequest->responses->count() : 0,
            'has_responses' => $documentRequest->responses ? $documentRequest->responses->count() > 0 : false,
            'responses' => $documentRequest->responses ?
                $documentRequest->responses->map(function ($response) {
                    return $this->transformResponse($response);
                }) : [],
            'created_at' => $documentRequest->created_at?->toISOString(),
            'updated_at' => $documentRequest->updated_at?->toISOString(),
        ];
    }

    /**
     * Transform response data for API
     */
    private function transformResponse($response): array
    {
        return [
            'id' => $response->id,
            'message' => $response->message,
            'response_type' => $response->response_type ?? 'general',
            'admin_name' => $response->admin_name ?? 'Administrator',
            'created_at' => $response->created_at?->toISOString(),
            'formatted_created_at' => $response->created_at ?
                $response->created_at->format('d/m/Y H:i') : null,
        ];
    }

    /**
     * Get urgency level label
     */
    private function getUrgencyLabel(string $urgency): string
    {
        return match ($urgency) {
            'low' => 'Faible',
            'medium' => 'Moyenne',
            'high' => 'Élevée',
            default => 'Non définie'
        };
    }

    /**
     * Get status label
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'En attente',
            'processing' => 'En cours de traitement',
            'completed' => 'Terminée',
            'rejected' => 'Rejetée',
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
