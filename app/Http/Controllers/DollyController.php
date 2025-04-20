<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use App\Models\Container;
use App\Models\DollyCommunication;
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
        $dollies = Dolly::with('type')->get();
        return view('dollies.index', compact('dollies'));
    }





    public function create()
    {
       return view('dollies.create');
    }



    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type_id' => 'required|exists:dolly_types,id',
        ]);

        $validatedData['is_public'] = false;
        $validatedData['category'] = 'mail';
        $validatedData['created_by'] = auth()->id();

        $dolly = Dolly::create($validatedData);
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
        $dolly->load('type','creator');
        return view('dollies.show', compact('dolly', 'records', 'mails', 'communications', 'rooms', 'containers', 'shelves', 'slip_records'));
    }





    public function edit(Dolly $dolly)
    {
        return view('dollies.edit', compact('dolly'));
    }






    public function update(Request $request, Dolly $dolly)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'type_id' => 'required|exists:dolly_types,id',
        ]);
        
        $validatedData['is_public'] = false;
        $validatedData['category'] = 'mail';
        $validatedData['created_by'] = auth()->id();

        $dolly->update($validatedData);
        return redirect()->route('dolly.index')->with('success', 'Dolly updated successfully.');
    }







    public function destroy(Dolly $dolly)
    {

        if ($dolly->mails()->exists() || $dolly->records()->exists() || $dolly->communications()->exists() || $dolly->slips()->exists() || $dolly->slipRecords()->exists() || $dolly->buildings()->exists() || $dolly->rooms()->exists() || $dolly->shelve()->exists()) {
           return redirect()->route('dolly.index')->with('error', 'Cannot delete Dolly because it has related records in other tables.');
        }
        $dolly->delete();
        return redirect()->route('dolly.index')->with('success', 'Dolly deleted successfully.');
    }






    public function apiList()
    {

        $dollies = Dolly::whereHas('type', function($query){
                $query->where('name', 'mail_transaction');
            })
            ->where('is_public', true )
            ->orWhere('created_by', auth()->id())->get();
        return response()->json($dollies);
    }




    public function apiCreate(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_id' => 'required|exists:dolly_types,id',
        ]);
        
        $validatedData['is_public'] = false;
        $validatedData['category'] = 'mail';
        $validatedData['created_by'] = auth()->id();

        $dolly = Dolly::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Chariot créé avec succès',
            'data' => $dolly
        ]);
    }


}


