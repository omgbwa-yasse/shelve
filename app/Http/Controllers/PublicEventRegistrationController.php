<?php

namespace App\Http\Controllers;

use App\Models\PublicEvent;
use App\Models\PublicEventRegistration;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicEventRegistrationController extends Controller
{
    /**
     * Display a listing of the registrations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $registrations = PublicEventRegistration::with(['event', 'user'])->get();
        return view('public-event-registrations.index', compact('registrations'));
    }

    /**
     * Show the form for creating a new registration.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $events = PublicEvent::all();
        $users = PublicUser::all();
        return view('public-event-registrations.create', compact('events', 'users'));
    }

    /**
     * Store a newly created registration in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:public_events,id',
            'user_id' => 'required|exists:public_users,id',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Check if registration already exists
        $exists = PublicEventRegistration::where('event_id', $validated['event_id'])
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'User is already registered for this event.');
        }

        // Create with current time
        $validated['registered_at'] = now();

        $registration = PublicEventRegistration::create($validated);

        return redirect()->route('public-event-registrations.index')
            ->with('success', 'Registration created successfully');
    }

    /**
     * Display the specified registration.
     *
     * @param  \App\Models\PublicEventRegistration  $registration
     * @return \Illuminate\Http\Response
     */
    public function show(PublicEventRegistration $registration)
    {
        $registration->load(['event', 'user']);
        return view('public-event-registrations.show', compact('registration'));
    }

    /**
     * Show the form for editing the specified registration.
     *
     * @param  \App\Models\PublicEventRegistration  $registration
     * @return \Illuminate\Http\Response
     */
    public function edit(PublicEventRegistration $registration)
    {
        $events = PublicEvent::all();
        $users = PublicUser::all();
        return view('public-event-registrations.edit', compact('registration', 'events', 'users'));
    }

    /**
     * Update the specified registration in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicEventRegistration  $registration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicEventRegistration $registration)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:public_events,id',
            'user_id' => 'required|exists:public_users,id',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Check if registration already exists for another entry
        $exists = PublicEventRegistration::where('event_id', $validated['event_id'])
            ->where('user_id', $validated['user_id'])
            ->where('id', '!=', $registration->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'User is already registered for this event.');
        }

        $registration->update($validated);

        return redirect()->route('public-event-registrations.index')
            ->with('success', 'Registration updated successfully');
    }

    /**
     * Remove the specified registration from storage.
     *
     * @param  \App\Models\PublicEventRegistration  $registration
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicEventRegistration $registration)
    {
        $registration->delete();

        return redirect()->route('public-event-registrations.index')
            ->with('success', 'Registration deleted successfully');
    }
}
