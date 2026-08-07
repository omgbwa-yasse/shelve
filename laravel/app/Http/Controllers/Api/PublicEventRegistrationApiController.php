<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicEventRegistration;
use App\Models\PublicEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for Public Event Registrations
 * Manages registrations for public events
 */
class PublicEventRegistrationApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PublicEventRegistration::with(['event', 'user']);

        // Filter by event if specified
        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by user if specified
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $registrations->items(),
            'pagination' => [
                'current_page' => $registrations->currentPage(),
                'per_page' => $registrations->perPage(),
                'total' => $registrations->total(),
                'last_page' => $registrations->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicEventRegistration $registration): JsonResponse
    {
        $registration->load(['event', 'user']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $registration->id,
                'event_id' => $registration->event_id,
                'user_id' => $registration->user_id,
                'full_name' => $registration->full_name,
                'email' => $registration->email,
                'phone' => $registration->phone,
                'notes' => $registration->notes,
                'status' => $registration->status,
                'status_label' => $this->getStatusLabel($registration->status),
                'event' => $registration->event ? [
                    'id' => $registration->event->id,
                    'title' => $registration->event->title,
                    'date' => $registration->event->date,
                    'location' => $registration->event->location,
                ] : null,
                'user' => $registration->user ? [
                    'id' => $registration->user->id,
                    'name' => $registration->user->name,
                    'email' => $registration->user->email,
                ] : null,
                'created_at' => $registration->created_at,
                'updated_at' => $registration->updated_at,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:public_events,id',
            'user_id' => 'nullable|exists:users,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'status' => 'in:pending,confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Check if user is already registered for this event
            $existingRegistration = PublicEventRegistration::where('event_id', $request->event_id)
                ->where('email', $request->email)
                ->first();

            if ($existingRegistration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous êtes déjà inscrit à cet événement',
                ], 409);
            }

            $registration = PublicEventRegistration::create([
                'event_id' => $request->event_id,
                'user_id' => $request->user_id,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'notes' => $request->notes,
                'status' => $request->get('status', 'pending'),
            ]);

            $registration->load(['event', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Inscription créée avec succès',
                'data' => $registration,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'inscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicEventRegistration $registration): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:pending,confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $registration->update($request->only([
                'full_name', 'email', 'phone', 'notes', 'status'
            ]));

            $registration->load(['event', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Inscription mise à jour avec succès',
                'data' => $registration,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'inscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicEventRegistration $registration): JsonResponse
    {
        try {
            $registration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inscription supprimée avec succès',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'inscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get registrations by event.
     */
    public function byEvent(PublicEvent $event, Request $request): JsonResponse
    {
        $query = $event->registrations()
            ->with(['user']);

        // Filter by status if specified
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $registrations->items(),
            'pagination' => [
                'current_page' => $registrations->currentPage(),
                'per_page' => $registrations->perPage(),
                'total' => $registrations->total(),
                'last_page' => $registrations->lastPage(),
            ],
            'statistics' => [
                'total_registrations' => $event->registrations()->count(),
                'confirmed_registrations' => $event->registrations()->confirmed()->count(),
                'pending_registrations' => $event->registrations()->pending()->count(),
                'cancelled_registrations' => $event->registrations()->cancelled()->count(),
            ],
        ]);
    }

    /**
     * Get registrations by user.
     */
    public function byUser(User $user, Request $request): JsonResponse
    {
        $query = $user->eventRegistrations()
            ->with(['event']);

        // Filter by status if specified
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $registrations->items(),
            'pagination' => [
                'current_page' => $registrations->currentPage(),
                'per_page' => $registrations->perPage(),
                'total' => $registrations->total(),
                'last_page' => $registrations->lastPage(),
            ],
        ]);
    }

    /**
     * Confirm a registration.
     */
    public function confirm(PublicEventRegistration $registration): JsonResponse
    {
        try {
            $registration->update(['status' => 'confirmed']);

            return response()->json([
                'success' => true,
                'message' => 'Inscription confirmée avec succès',
                'data' => [
                    'status' => $registration->status,
                    'status_label' => $this->getStatusLabel($registration->status),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a registration.
     */
    public function cancel(PublicEventRegistration $registration): JsonResponse
    {
        try {
            $registration->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Inscription annulée avec succès',
                'data' => [
                    'status' => $registration->status,
                    'status_label' => $this->getStatusLabel($registration->status),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get event registration statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = PublicEventRegistration::query();

        // Filter by date range if specified
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $statistics = [
            'total_registrations' => $query->count(),
            'confirmed_registrations' => (clone $query)->confirmed()->count(),
            'pending_registrations' => (clone $query)->pending()->count(),
            'cancelled_registrations' => (clone $query)->cancelled()->count(),
            'registrations_by_status' => [
                'pending' => (clone $query)->pending()->count(),
                'confirmed' => (clone $query)->confirmed()->count(),
                'cancelled' => (clone $query)->cancelled()->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get status label in French.
     */
    private function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'cancelled' => 'Annulée',
        ];

        return $labels[$status] ?? $status;
    }
}
