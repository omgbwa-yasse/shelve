<?php

namespace App\Http\Controllers;

use App\Models\ReservationRecord;
use App\Models\Reservation;
use App\Models\ReservationStatus;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ReservationRecordController extends Controller
{
    public function index(INT $id)
    {
        $reservation = Reservation ::findOrFail($id);
        $reservationRecords = ReservationRecord::where('reservation_id', $reservation->id)->get();
        $reservationRecords->load('reservation', 'record','communication');

        return view('Reservations.records.index', compact('reservationRecords','reservation'));
    }




    public function create(INT $id)
    {
        $reservation = Reservation::findOrFail($id);
        $records = Record::all();
        $users = User::all();
        return view('Reservations.records.create', compact('reservation', 'records', 'users'));
    }




    public function show(INT $id, INT $idRecord)
    {
        $reservationRecord = ReservationRecord::findOrFail($idRecord);
        $reservationRecord->load('record','reservation');
        $reservation = Reservation::findOrFail($id);
        return view('reservations.records.show', compact('reservationRecord', 'reservation'));
    }





    public function edit(Reservation $reservation, ReservationRecord $reservationRecord)
    {
        $records = Record::all();
        $users = User::all();
        return view('reservations.records.edit', compact('reservationRecord', 'reservation', 'records', 'users'));
    }




    public function store(Request $request, INT $id)
    {
        $request->validate([
            'record_id' => 'required|exists:records,id',
            'is_original' => 'required|boolean',
            'reservation_date' => 'required|date',
        ]);

        $reservation = Reservation::findOrFail($id);

        $ReservationRecord = ReservationRecord::create([
            'reservation_id' => $reservation->id,
            'record_id' => $request->record_id,
            'is_original' => $request->is_original,
            'reservation_date' => $request->reservation_date,
            'operator_id' => Auth::id(),
        ]);

        return redirect()->route('reservations.records.index', $reservation )->with('success', 'Reservation created successfully.');
    }




    public function update(Request $request, ReservationRecord $ReservationRecord)
    {
        $request->validate([
            'record_id' => 'required|exists:records,id',
            'is_original' => 'required|boolean',
            'reservation_date' => 'required|date',
        ]);

        $ReservationRecord->update($request->all());

        return redirect()->route('reservations.records.index')->with('success', 'Reservation updated successfully.');
    }




    public function destroy(INT $id, ReservationRecord $ReservationRecord)
    {
        $ReservationRecord->delete();
        return redirect()->route('reservations.records.index', $id)->with('success', 'Reservation record deleted successfully.');
    }
}



