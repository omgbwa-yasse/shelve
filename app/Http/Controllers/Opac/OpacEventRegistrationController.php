<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\OpacEvent;
use App\Models\OpacEventRegistration;
use App\Models\OpacUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OpacEventRegistrationController extends Controller
{
    /**
     * Display a listing of the registrations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $registrations = OpacEventRegistration::with(['event', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Event registrations retrieved successfully',
            'data' => $registrations
        ], 200);
    }

    /**
     * Show the form for creating a new registration.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $events = OpacEvent::all();
        $users = OpacUser::all();
        return view('opac.event-registrations.create', compact('events', 'users'));
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
            'event_id' => 'required|exists:opac_events,id',
            'user_id' => 'required|exists:opac_users,id',
            'status' => 'required|in:pending,confirmed,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Check if registration already exists
        $exists = OpacEventRegistration::where('event_id', $validated['event_id'])
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already registered for this event.'
            ], 400);
        }

        // Create with current time
        $validated['registered_at'] = now();

        $registration = OpacEventRegistration::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Event registration created successfully',
            'data' => $registration
        ], 201);
    }

    /**
     * Display the specified registration.
     *
     * @param  \App\Models\OpacEventRegistration  $registration
     * @return \Illuminate\Http\Response
     */
    public function show(OpacEventRegistration $registration)
    {
        $registration->load(['event', 'user']);
        return response()->json([
            'status' => 'success',
            'message' => 'Event registration details retrieved successfully',
            'data' => $registration
        ], 200);
    }

    /**
     * Show the form for editing the specified registration.
     *
     * @param  \App\Models\OpacEventRegistration  $registration
     * @return \Illuminate\Http\Response
     */
    public function edit(OpacEventRegistration $registration)
    {
        $events = OpacEvent::all();
        $users = OpacUser::all();
        return view('opac.event-registrations.edit', compact('registration', 'events', 'users'));
    }

    /**
     * Update the specified registration in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OpacEventRegistration  $registration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OpacEventRegistration $registration)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Check if registration already exists for another entry
        $exists = OpacEventRegistration::where('event_id', $validated['event_id'])
            ->where('user_id', $validated['user_id'])
            ->where('id', '!=', $registration->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already registered for this event.'
            ], 400);
        }

        $registration->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Event registration updated successfully',
            'data' => $registration
        ], 200);
    }

    /**
     * Remove the specified registration from storage.
     *
     * @param  \App\Models\OpacEventRegistration  $registration
     * @return \Illuminate\Http\Response
     */
    public function destroy(OpacEventRegistration $registration)
    {
        $registration->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Event registration deleted successfully'
        ], 200);
    }
}
