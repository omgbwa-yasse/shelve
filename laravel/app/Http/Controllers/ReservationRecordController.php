<?php

namespace App\Http\Controllers;

use App\Models\ReservationRecord;
use App\Models\Reservation;
use App\Models\Organisation;
use App\Models\RecordPhysical;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ReservationRecordController extends Controller
{
    public function index(Reservation $reservation)
    {
        $reservationRecords = ReservationRecordPhysical::where('reservation_id', $reservation->id)->get();
        $reservationRecords->load('reservation', 'record','communication');

        return view('communications.reservations.records.index', compact('reservationRecords','reservation'));
    }




    public function create(Reservation $reservation)
    {
        // Ne pas charger tous les records, la recherche se fera via AJAX
        return view('communications.reservations.records.create', compact('reservation'));
    }




    public function show(Reservation $reservation, ReservationRecord $reservationRecord)
    {
        $reservationRecord->load('record','reservation');
        return view('communications.reservations.records.show', compact('reservationRecord', 'reservation'));
    }





    public function edit(Reservation $reservation, ReservationRecord $reservationRecord)
    {
        $records = RecordPhysical::select('id', 'code', 'name')
            ->orderBy('name')
            ->get();

        return view('communications.reservations.records.edit', compact('reservationRecord', 'reservation', 'records'));
    }




    public function store(Request $request, Reservation $reservation)
    {
        $request->validate([
            'record_id' => 'required|exists:records,id',
            'is_original' => 'required|boolean',
            'reservation_date' => 'required|date',
        ]);

        ReservationRecordPhysical::create([
            'reservation_id' => $reservation->id,
            'record_id' => $request->record_id,
            'is_original' => $request->is_original,
            'reservation_date' => $request->reservation_date,
            'operator_id' => Auth::id(),
        ]);

        return redirect()->route('communications.reservations.records.index', $reservation)->with('success', 'Reservation created successfully.');
    }




    public function update(Request $request, Reservation $reservation, ReservationRecord $reservationRecord)
    {
        $request->validate([
            'record_id' => 'required|exists:records,id',
            'is_original' => 'required|boolean',
            'reservation_date' => 'required|date',
        ]);

        $reservationRecord->update($request->all());

        return redirect()->route('communications.reservations.records.index', $reservation)->with('success', 'Reservation updated successfully.');
    }




    public function destroy(Reservation $reservation, ReservationRecord $reservationRecord)
    {
        $reservationRecord->delete();
        return redirect()->route('communications.reservations.records.index', $reservation)->with('success', 'Reservation record deleted successfully.');
    }
}



