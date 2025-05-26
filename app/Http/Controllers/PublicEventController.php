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
        return view('public-events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('public-events.create');
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

        return redirect()->route('public-events.index')
            ->with('success', 'Event created successfully');
    }

    /**
     * Display the specified event.
     *
     * @param  \App\Models\PublicEvent  $publicEvent
     * @return \Illuminate\Http\Response
     */
    public function show(PublicEvent $publicEvent)
    {
        $publicEvent->load('registrations.user');
        return view('public-events.show', compact('publicEvent'));
    }

    /**
     * Show the form for editing the specified event.
     *
     * @param  \App\Models\PublicEvent  $publicEvent
     * @return \Illuminate\Http\Response
     */
    public function edit(PublicEvent $publicEvent)
    {
        return view('public-events.edit', compact('publicEvent'));
    }

    /**
     * Update the specified event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicEvent  $publicEvent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicEvent $publicEvent)
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

        $publicEvent->update($validated);

        return redirect()->route('public-events.index')
            ->with('success', 'Event updated successfully');
    }

    /**
     * Remove the specified event from storage.
     *
     * @param  \App\Models\PublicEvent  $publicEvent
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicEvent $publicEvent)
    {
        $publicEvent->delete();

        return redirect()->route('public-events.index')
            ->with('success', 'Event deleted successfully');
    }

    /**
     * Display a list of users registered for the event
     *
     * @param  \App\Models\PublicEvent  $publicEvent
     * @return \Illuminate\Http\Response
     */
    public function registrations(PublicEvent $publicEvent)
    {
        $registrations = $publicEvent->registrations()->with('user')->get();
        return view('public-events.registrations', compact('publicEvent', 'registrations'));
    }
}
