<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicFeedback;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for Public Feedback
 * Handles feedback submission and management for the public portal
 */
class PublicFeedbackApiController extends Controller
{
    // Message constants
    private const FEEDBACK_SUBMITTED = 'Feedback submitted successfully';

    // Validation rule constants
    private const REQUIRED_STRING = 'required|string';
    private const REQUIRED_STRING_MAX_255 = 'required|string|max:255';

    private const STORE_RULES = [
        'title' => self::REQUIRED_STRING_MAX_255,
        'content' => self::REQUIRED_STRING,
        'type' => 'required|in:bug,feature,improvement,other',
        'priority' => 'required|in:low,medium,high',
        'contact_email' => 'required|email',
        'contact_name' => self::REQUIRED_STRING_MAX_255,
    ];

    /**
     * API: Store new feedback
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(self::STORE_RULES);
        $validated['status'] = 'new';

        // Associate feedback with authenticated user if available
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        $feedback = PublicFeedback::create($validated);

        return $this->successResponse(
            self::FEEDBACK_SUBMITTED,
            $this->transformFeedback($feedback),
            201
        );
    }

    /**
     * API: Get user's feedback
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $feedbacks = PublicFeedback::with(['comments'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => collect($feedbacks->items())->map(function ($feedback) {
                return $this->transformFeedback($feedback);
            })->toArray(),
            'pagination' => [
                'current_page' => $feedbacks->currentPage(),
                'last_page' => $feedbacks->lastPage(),
                'per_page' => $feedbacks->perPage(),
                'total' => $feedbacks->total(),
            ]
        ]);
    }

    /**
     * Transform feedback data for API response
     */
    private function transformFeedback($feedback): array
    {
        return [
            'id' => $feedback->id,
            'title' => $feedback->title,
            'content' => $feedback->content,
            'type' => $feedback->type,
            'type_label' => $this->getTypeLabel($feedback->type),
            'priority' => $feedback->priority,
            'priority_label' => $this->getPriorityLabel($feedback->priority),
            'status' => $feedback->status,
            'status_label' => $this->getStatusLabel($feedback->status),
            'contact_email' => $feedback->contact_email,
            'contact_name' => $feedback->contact_name,
            'user_id' => $feedback->user_id,
            'comments_count' => $feedback->comments ? $feedback->comments->count() : 0,
            'has_comments' => $feedback->comments ? $feedback->comments->count() > 0 : false,
            'comments' => $feedback->comments ?
                $feedback->comments->map(function ($comment) {
                    return $this->transformComment($comment);
                }) : [],
            'created_at' => $feedback->created_at?->toISOString(),
            'updated_at' => $feedback->updated_at?->toISOString(),
            'formatted_created_at' => $feedback->created_at ?
                $feedback->created_at->format('d/m/Y H:i') : null,
        ];
    }

    /**
     * Transform comment data for API
     */
    private function transformComment($comment): array
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'admin_name' => $comment->admin_name ?? 'Administrator',
            'is_public' => $comment->is_public ?? false,
            'created_at' => $comment->created_at?->toISOString(),
            'formatted_created_at' => $comment->created_at ?
                $comment->created_at->format('d/m/Y H:i') : null,
        ];
    }

    /**
     * Get feedback type label
     */
    private function getTypeLabel(string $type): string
    {
        return match ($type) {
            'bug' => 'Problème',
            'feature' => 'Nouvelle fonctionnalité',
            'improvement' => 'Amélioration',
            'other' => 'Autre',
            default => 'Non défini'
        };
    }

    /**
     * Get priority level label
     */
    private function getPriorityLabel(string $priority): string
    {
        return match ($priority) {
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
            'new' => 'Nouveau',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
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
