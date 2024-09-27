<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use App\Models\Container;
use App\Models\Mail;
use App\Models\Record;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\SlipRecord;
use Illuminate\Http\Request;
use App\Models\Dolly;


class DollyController extends Controller
{
    public function index()
    {
        $dollies = Dolly::with('type', 'mails', 'records', 'communications', 'slips', 'slipRecords', 'containers', 'rooms', 'shelve')->get();
        return view('dollies.index', compact('dollies'));
    }

    public function create()
    {
       return view('dollies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'type_id' => 'required|exists:dolly_types,id',
        ]);

        $dolly = Dolly::create($request->all());
        return redirect()->route('dolly.index')->with('success', 'Dolly created successfully.');
    }


    public function show(Dolly $dolly)
    {
        $records = Record::all();
        $mails = Mail::all();
        $communications = Communication::all();
        $rooms = Room::all();
        $containers = Container::all();
        $shelves = Shelf::all();
        $slip_records = SlipRecord::all();
        $dolly->load('type');
        return view('dollies.show', compact('dolly', 'records', 'mails', 'communications', 'rooms', 'containers', 'shelves', 'slip_records'));
    }
    public function createWithRecords(Request $request)
    {
        $recordIds = $request->input('records');

        // Créer un nouveau Dolly
        $dolly = Dolly::create([
            'name' => 'Chariot ' . now()->format('Y-m-d H:i:s'),
            'description' => 'Créé automatiquement',
            'type_id' => 1, // Assurez-vous d'avoir un type par défaut
        ]);

        // Associer les enregistrements au Dolly
        $dolly->records()->attach($recordIds);

        return response()->json(['success' => true]);
    }
    public function edit(Dolly $dolly)
    {
        return view('dollies.edit', compact('dolly'));
    }



    public function update(Request $request, Dolly $dolly)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'type_id' => 'required|exists:dolly_types,id',
        ]);

        $dolly->update($request->all());
        return redirect()->route('dolly.index')->with('success', 'Dolly updated successfully.');
    }



    public function destroy(Dolly $dolly)
    {

        if ($dolly->mail()->exists() || $dolly->record()->exists() || $dolly->communication()->exists() || $dolly->slip()->exists() || $dolly->slipRecord()->exists() || $dolly->building()->exists() || $dolly->room()->exists() || $dolly->shelf()->exists()) {
           return redirect()->route('dolly.index')->with('error', 'Cannot delete Dolly because it has related records in other tables.');
        }
        $dolly->delete();
        return redirect()->route('dolly.index')->with('success', 'Dolly deleted successfully.');
    }






    public function addRecord(Request $request, Dolly $dolly)
    {
        $dolly->records()->attach($request->record_id);
        return redirect()->route('dolly.show', $dolly);
    }

    public function addMail(Request $request, Dolly $dolly)
    {
        $dolly->mails()->attach($request->mail_id);
        return redirect()->route('dolly.show', $dolly);
    }

    public function addCommunication(Request $request, Dolly $dolly)
    {
        $dolly->communications()->attach($request->communication_id);
        return redirect()->route('dolly.show', $dolly);
    }

    public function addRoom(Request $request, Dolly $dolly)
    {
        $dolly->rooms()->attach($request->room_id);
        return redirect()->route('dolly.show', $dolly);
    }

    public function addContainer(Request $request, Dolly $dolly)
    {
        $dolly->containers()->attach($request->container_id);
        return redirect()->route('dolly.show', $dolly);
    }

    public function addShelve(Request $request, Dolly $dolly)
    {
        $dolly->shelves()->attach($request->shelve_id);
        return redirect()->route('dolly.show', $dolly);
    }

    public function addSlipRecord(Request $request, Dolly $dolly)
    {
        $dolly->slipRecords()->attach($request->slip_record_id);
        return redirect()->route('dolly.show', $dolly);
    }

    public function removeRecord(Dolly $dolly, Record $record)
    {
        $dolly->records()->detach($record->id);
        return redirect()->route('dolly.show', $dolly);
    }

    public function removeMail(Dolly $dolly, Mail $mail)
    {
        $dolly->mails()->detach($mail->id);
        return redirect()->route('dolly.show', $dolly);
    }
}


