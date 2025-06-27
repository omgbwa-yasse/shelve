<?php

namespace App\Http\Controllers;

use App\Models\ReservationStatus;
use Illuminate\Http\Request;

class ReservationStatusController extends Controller
{
    public function index()
    {
        $statuses = ReservationStatus::with('reservations')->get();
        return view('communications.reservations.statuses.index', compact('statuses'));
    }

    public function show(ReservationStatus $status)
    {
        return view('communications.reservations.statuses.show', compact('status'));
    }

    public function create()
    {
        return view('communications.reservations.statuses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:reservation_statuses|max:50',
            'description' => 'nullable|string',
        ]);

        ReservationStatus::create($request->all());

        return redirect()->route('communications.reservations.statuses.index')
            ->with('success', 'Reservation status created successfully.');
    }

    public function edit(ReservationStatus $status)
    {
        return view('communications.reservations.statuses.edit', compact('status'));
    }

    public function update(Request $request, ReservationStatus $status)
    {
        $request->validate([
            'name' => 'required|unique:reservation_statuses,name,'.$status->id.'|max:50',
            'description' => 'nullable|string',
        ]);

        $status->update($request->all());

        return redirect()->route('communications.reservations.statuses.index')
            ->with('success', 'Reservation status updated successfully.');
    }

    public function destroy(ReservationStatus $status)
    {
        $status->delete();

        return redirect()->route('communications.reservations.statuses.index')
            ->with('success', 'Reservation status deleted successfully.');
    }
}
