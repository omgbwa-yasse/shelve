<?php

namespace App\Http\Controllers;

use App\Models\PublicEvent;
use App\Models\PublicEventRegistration;
use App\Models\PublicUser;
use Illuminate\Http\Request;

class PublicEventRegistrationController extends Controller
{
    /**
     * Display a listing of the registrations.
     */
    public function index()
    {
        $registrations = PublicEventRegistration::with(['event', 'user'])->paginate(10);
        return view('public.event-registrations.index', compact('registrations'));
    }

    /**
     * Show the form for creating a new registration.
     */
    public function create()
    {
        $events = PublicEvent::where('status', 'published')->latest()->get();
        return view('public.event-registrations.create', compact('events'));
    }

    /**
     * Store a newly created registration in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:public_events,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if registration already exists
        $exists = PublicEventRegistration::where('event_id', $validated['event_id'])
            ->where('email', $validated['email'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Cette adresse email est déjà inscrite à cet événement.');
        }

        $validated['status'] = 'pending';
        $validated['registered_at'] = now();

        PublicEventRegistration::create($validated);

        return redirect()->route('public.event-registrations.index')
            ->with('success', 'Inscription créée avec succès.');
    }

    /**
     * Display the specified registration.
     */
    public function show(PublicEventRegistration $registration)
    {
        $registration->load(['event', 'user']);
        return view('public.event-registrations.show', compact('registration'));
    }

    /**
     * Show the form for editing the specified registration.
     */
    public function edit(PublicEventRegistration $registration)
    {
        $events = PublicEvent::latest()->get();
        return view('public.event-registrations.edit', compact('registration', 'events'));
    }

    /**
     * Update the specified registration in storage.
     */
    public function update(Request $request, PublicEventRegistration $registration)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:public_events,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:pending,confirmed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if registration already exists for another entry
        $exists = PublicEventRegistration::where('event_id', $validated['event_id'])
            ->where('email', $validated['email'])
            ->where('id', '!=', $registration->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Cette adresse email est déjà inscrite à cet événement.');
        }

        $registration->update($validated);

        return redirect()->route('public.event-registrations.index')
            ->with('success', 'Inscription modifiée avec succès.');
    }

    /**
     * Remove the specified registration from storage.
     */
    public function destroy(PublicEventRegistration $registration)
    {
        $registration->delete();

        return redirect()->route('public.event-registrations.index')
            ->with('success', 'Inscription supprimée avec succès.');
    }
}
