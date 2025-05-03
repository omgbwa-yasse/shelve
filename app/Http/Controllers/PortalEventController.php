<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PortalEventController extends Controller
{
    /**
     * Display a listing of the events.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $events = Event::where('status', 'published')
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Display the specified event.
     *
     * @param Event $event
     * @return JsonResponse
     */
    public function show(Event $event): JsonResponse
    {
        if ($event->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $event->load(['registrations'])
        ]);
    }

    /**
     * Register a user for an event.
     *
     * @param Request $request
     * @param Event $event
     * @return JsonResponse
     */
    public function register(Request $request, Event $event): JsonResponse
    {
        if ($event->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Event is not available for registration'
            ], 400);
        }

        if ($event->isFull()) {
            return response()->json([
                'success' => false,
                'message' => 'Event is full'
            ], 400);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string'
        ]);

        $registration = $event->registrations()->create([
            'user_id' => auth()->id(),
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'notes' => $validated['notes'],
            'status' => 'registered'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully registered for the event',
            'data' => $registration
        ]);
    }

    /**
     * Get upcoming events.
     *
     * @return JsonResponse
     */
    public function upcoming(): JsonResponse
    {
        $events = Event::where('status', 'published')
            ->where('start_date', '>', now())
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Get past events.
     *
     * @return JsonResponse
     */
    public function past(): JsonResponse
    {
        $events = Event::where('status', 'published')
            ->where('end_date', '<', now())
            ->orderBy('end_date', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Search events.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = Event::where('status', 'published');

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        $events = $query->orderBy('start_date', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }
}
