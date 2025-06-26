<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicEvent;
use App\Models\PublicEventRegistration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PublicEventApiController extends Controller
{
    /**
     * Get paginated events for frontend
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'is_online' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = PublicEvent::with(['registrations']);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->get('date_to'));
        }

        if ($request->filled('is_online')) {
            $query->where('is_online', $request->boolean('is_online'));
        }

        // Tri par défaut : événements à venir en premier
        $query->orderBy('start_date', 'asc');

        // Pagination
        $perPage = min($request->get('per_page', 10), 50);
        $events = $query->paginate($perPage);

        // Transform data for consistent API response
        $transformedEvents = $events->getCollection()->map(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'location' => $event->location,
                'is_online' => $event->is_online,
                'online_link' => $event->online_link,
                'max_participants' => $event->max_participants,
                'registration_count' => $event->registrations->count(),
                'is_full' => $event->max_participants && $event->registrations->count() >= $event->max_participants,
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedEvents,
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'from' => $events->firstItem(),
                'to' => $events->lastItem(),
            ]
        ]);
    }

    /**
     * Get single event details
     */
    public function show(PublicEvent $event): JsonResponse
    {
        $event->load(['registrations.user']);

        $transformedEvent = [
            'id' => $event->id,
            'name' => $event->name,
            'description' => $event->description,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'is_online' => $event->is_online,
            'online_link' => $event->online_link,
            'max_participants' => $event->max_participants,
            'registration_count' => $event->registrations->count(),
            'is_full' => $event->max_participants && $event->registrations->count() >= $event->max_participants,
            'created_at' => $event->created_at,
            'updated_at' => $event->updated_at,
            'registrations' => $event->registrations->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'user' => [
                        'id' => $registration->user->id,
                        'name' => $registration->user->name,
                        'email' => $registration->user->email,
                    ],
                    'registered_at' => $registration->created_at,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $transformedEvent
        ]);
    }

    /**
     * Register user to an event
     */
    public function register(Request $request, PublicEvent $event): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if event is full
        if ($event->max_participants && $event->registrations->count() >= $event->max_participants) {
            return response()->json([
                'success' => false,
                'message' => 'Event is full'
            ], 400);
        }

        // Check if user is already registered
        $existingRegistration = PublicEventRegistration::where('event_id', $event->id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'success' => false,
                'message' => 'User is already registered for this event'
            ], 400);
        }

        // Create registration
        $registration = PublicEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $request->user_id,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully registered for the event',
            'data' => $registration
        ]);
    }

    /**
     * Get event registrations
     */
    public function registrations(PublicEvent $event): JsonResponse
    {
        $registrations = $event->registrations()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $transformedRegistrations = $registrations->map(function ($registration) {
            return [
                'id' => $registration->id,
                'user' => [
                    'id' => $registration->user->id,
                    'name' => $registration->user->name,
                    'email' => $registration->user->email,
                ],
                'notes' => $registration->notes,
                'registered_at' => $registration->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedRegistrations,
            'total' => $registrations->count()
        ]);
    }

    /**
     * Cancel registration
     */
    public function cancelRegistration(Request $request, PublicEvent $event): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $registration = PublicEventRegistration::where('event_id', $event->id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Registration not found'
            ], 404);
        }

        $registration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registration cancelled successfully'
        ]);
    }
}
