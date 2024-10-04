<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Communication;
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



    public function show($id)
    {
        $reservation = reservation::findOrFail($id);
        return view('reservations.show', compact('reservation'));
    }


    public function approved(Request $request)
    {
        $reservation = reservation::findOrFail($request->input('id'));

        $communication = Communication::create([
            'code' => $reservation->code,
            'name' => $reservation->name,
            'content' => $reservation->input('content'),
            'operator_id' => Auth()->user()->id,
            'user_id' => $reservation->user_id,
            'user_organisation_id' => $reservation->user_organisation_id,
            'operator_organisation_id' => Auth()->user()->organisation->id,
            'return_date' => date('y-m-d', strtotime("+14 days")),
            'status_id' => 1,
        ]);

        foreach($reservation->records as $record){
            $communication->records->attach($record->id);
            $reservation->records->detach($record->id);
        }

        $reservation->delete();

        return view('communications.show', compact('communication'));
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
            'content' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'user_organisation_id' => 'required|exists:organisations,id',
        ]);


        Reservation::create([
            'code' => $request->code,
            'name' => $request->name,
            'content' => $request->input('content'),
            'operator_id' => Auth()->user()->id,
            'user_id' => $request->user_id,
            'user_organisation_id' => $request->user_organisation_id,
            'operator_organisation_id' => Auth()->user()->organisation->id,
            'return_date' => $request->return_date,
            'status_id' => 1, // Examen
        ]);

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
            'content' => 'nullable|sting',
            'user_id' => 'required|exists:users,id',
            'user_organisation_id' => 'required|exists:organisations,id',
        ]);

        $reservation->update([
            'code' => $request->code,
            'name' => $request->name,
            'content' => $request->input('content'),
            'operator_id' => Auth()->user()->id,
            'operator_organisation_id' => Auth()->user()->organisation->id,
            'user_id' => $request->user_id,
            'user_organisation_id' => $request->user_organisation_id,
            'status_id' => 1,
        ]);

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
