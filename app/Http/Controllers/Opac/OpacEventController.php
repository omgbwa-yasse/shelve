<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\PublicEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpacEventController extends Controller
{
    /**
     * Display a listing of the events.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = PublicEvent::orderBy('start_date')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Events retrieved successfully',
            'data' => $events
        ], 200);
    }

    /**
     * Show the form for creating a new event.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('opac.events.create');
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'is_published' => 'boolean',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('opac/events');
            $validated['featured_image_path'] = $path;
        }

        $event = PublicEvent::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Event created successfully',
            'data' => $event
        ], 201);
    }

    /**
     * Display the specified event.
     *
     * @param  \App\Models\PublicEvent  $event
     * @return \Illuminate\Http\Response
     */
    public function show(PublicEvent $event)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Event details retrieved successfully',
            'data' => $event->load('registrations')
        ], 200);
    }

    /**
     * Show the form for editing the specified event.
     *
     * @param  \App\Models\PublicEvent  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(PublicEvent $event)
    {
        return view('opac.events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicEvent  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicEvent $event)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'location' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'is_published' => 'boolean',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($event->featured_image_path) {
                Storage::delete($event->featured_image_path);
            }
            $path = $request->file('featured_image')->store('opac/events');
            $validated['featured_image_path'] = $path;
        }

        $event->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Event updated successfully',
            'data' => $event
        ], 200);
    }

    /**
     * Remove the specified event from storage.
     *
     * @param  \App\Models\PublicEvent  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicEvent $event)
    {
        if ($event->featured_image_path) {
            Storage::delete($event->featured_image_path);
        }

        $event->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Event deleted successfully'
        ], 200);
    }

    /**
     * Display a list of users registered for the event
     *
     * @param  \App\Models\PublicEvent  $event
     * @return \Illuminate\Http\Response
     */
    public function registrations(PublicEvent $event)
    {
        $registrations = $event->registrations()->with('user')->get();
        return view('opac.events.registrations', compact('event', 'registrations'));
    }
}
