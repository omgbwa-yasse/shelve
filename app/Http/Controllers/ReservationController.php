<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\ReservationStatus;
use App\Models\User;
use App\Models\Organisation;



class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with('operator', 'user', 'status', 'userOrganisation', 'operatorOrganisation')->get();
        return view('reservations.index', compact('reservations'));
    }

    public function show(INT $id)
    {
        $reservation = reservation::findOrFail($id);
        return view('reservations.show', compact('reservation'));
    }

    public function create()
    {
        $operators = User::all();
        $users = User::all();
        $statuses = ReservationStatus::all();
        $organisations = Organisation::all();
        return view('reservations.create', compact('operators', 'users', 'statuses', 'organisations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:reservations|max:10',
            'name' => 'required|string|max:200',
            'content' => 'nullable|text',
            'operator_id' => 'required|exists:users,id',
            'operator_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'required|exists:users,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'status_id' => 'required|exists:reservation_statuses,id',
        ]);

        Reservation::create($request->all());

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation created successfully.');
    }

    public function edit(INT $id)
    {
        $reservation = reservation::findOrFail($id);
        $operators = User::all();
        $users = User::all();
        $statuses = ReservationStatus::all();
        $organisations = Organisation::all();
        return view('reservations.edit', compact('reservation', 'operators', 'users', 'statuses', 'organisations'));
    }

    public function update(Request $request, INT $id)
    {
        $reservation = reservation::findOrFail($id);
        $request->validate([
            'code' => 'required|unique:reservations,code,'.$reservation->id.'|max:10',
            'name' => 'required|string|max:200',
            'content' => 'nullable|text',
            'operator_id' => 'required|exists:users,id',
            'operator_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'required|exists:users,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'status_id' => 'required|exists:reservation_statuses,id',
        ]);

        $reservation->update($request->all());

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation updated successfully.');
    }

    public function destroy(INT $id)
    {
        $reservation = reservation::findOrFail($id);
        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation deleted successfully.');
    }
}
