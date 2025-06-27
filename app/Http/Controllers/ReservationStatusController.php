<?php

namespace App\Http\Controllers;

use App\Models\ReservationStatus;
use Illuminate\Http\Request;

class ReservationStatusController extends Controller
{
    public function index()
    {
        $statuses = ReservationStatus::with('reservations')->get();
        return view('settings.reservation-statuses.index', compact('statuses'));
    }

    public function show(ReservationStatus $reservationStatus)
    {
        return view('settings.reservation-statuses.show', compact('reservationStatus'));
    }

    public function create()
    {
        return view('settings.reservation-statuses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:reservation_statuses|max:50',
            'description' => 'nullable|string',
        ]);

        ReservationStatus::create($request->all());

        return redirect()->route('reservation-status.index')
            ->with('success', 'Reservation status created successfully.');
    }

    public function edit(ReservationStatus $reservationStatus)
    {
        return view('settings.reservation-statuses.edit', compact('reservationStatus'));
    }

    public function update(Request $request, ReservationStatus $reservationStatus)
    {
        $request->validate([
            'name' => 'required|unique:reservation_statuses,name,'.$reservationStatus->id.'|max:50',
            'description' => 'nullable|string',
        ]);

        $reservationStatus->update($request->all());

        return redirect()->route('reservation-status.index')
            ->with('success', 'Reservation status updated successfully.');
    }

    public function destroy(ReservationStatus $reservationStatus)
    {
        $reservationStatus->delete();

        return redirect()->route('reservation-status.index')
            ->with('success', 'Reservation status deleted successfully.');
    }
}
