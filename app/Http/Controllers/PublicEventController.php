<?php

namespace App\Http\Controllers;

use App\Models\PublicEvent;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicEventController extends Controller
{
    /**
     * Display a listing of the events.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = PublicEvent::all();
        return view('public.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('public.events.create');
    }

    /**
     * Store a newly created event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'is_online' => 'boolean',
            'online_link' => 'nullable|url|required_if:is_online,1',
        ]);

        $event = PublicEvent::create($validated);

        return redirect()->route('public.events.index')
            ->with('success', 'Event created successfully');
    }

    /**
     * Display the specified event.
     *
     * @param  \App\Models\PublicEvent  $publicEvent
     * @return \Illuminate\Http\Response
     */
    public function show(PublicEvent $event)
    {
        $event->load('registrations.user');
        return view('public.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     *
     * @param  \App\Models\PublicEvent  $publicEvent
     * @return \Illuminate\Http\Response
     */
    public function edit(PublicEvent $event)
    {
        return view('public.events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicEvent  $publicEvent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicEvent $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'is_online' => 'boolean',
            'online_link' => 'nullable|url|required_if:is_online,1',
        ]);

        $event->update($validated);

        return redirect()->route('public.events.index')
            ->with('success', 'Event updated successfully');
    }

    /**
     * Remove the specified event from storage.
     *
     * @param  \App\Models\PublicEvent  $publicEvent
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicEvent $event)
    {
        $event->delete();

        return redirect()->route('public.events.index')
            ->with('success', 'Event deleted successfully');
    }

    /**
     * Display a list of users registered for the event
     *
     * @param  \App\Models\PublicEvent  $publicEvent
     * @return \Illuminate\Http\Response
     */
    public function registrations(PublicEvent $event)
    {
        $registrations = $event->registrations()->with('user')->get();
        return view('public.events.registrations', compact('event', 'registrations'));
    }

    // ========================================
    // API METHODS pour l'interface React
    // ========================================

    /**
     * API: Get paginated events for React frontend
     */
    public function apiIndex(Request $request)
    {
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

        return response()->json([
            'success' => true,
            'data' => $events->items(),
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
     * API: Get single event for React frontend
     */
    public function apiShow(PublicEvent $event)
    {
        $event->load(['registrations']);

        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    /**
     * API: Register to an event
     */
    public function apiRegister(Request $request, PublicEvent $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        // Vérifier si l'utilisateur est déjà inscrit
        $existingRegistration = $event->registrations()
            ->where('email', $validated['email'])
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà inscrit à cet événement.'
            ], 422);
        }

        // Créer l'inscription
        $registration = $event->registrations()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie à l\'événement.',
            'data' => $registration
        ]);
    }

    /**
     * API: Get event registrations
     */
    public function apiRegistrations(PublicEvent $event)
    {
        $registrations = $event->registrations()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $registrations,
            'total' => $registrations->count()
        ]);
    }
}
