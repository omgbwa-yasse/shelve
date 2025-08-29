<?php

namespace App\Http\Controllers;
use App\Models\Communication;
use App\Models\Container;
use App\Models\DollyCommunication;
use Illuminate\Support\Facades\Auth;
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
        $dollies = Dolly::where('owner_organisation_id', Auth::user()->current_organisation_id)
            ->paginate(25);
        return view('dollies.index', compact('dollies'));
    }





    public function create()
    {
        $categories = Dolly::categories();
        return view('dollies.create', compact('categories'));
    }



    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|exists:dollies,category',
        ]);
        $validatedData['is_public'] = false;
        $validatedData['created_by'] = Auth::user()->getAuthIdentifier;
        $validatedData['owner_organisation_id'] = Auth::user()->current_organisation_id;

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
        $dolly->load('creator','ownerOrganisation');
        return view('dollies.show', compact('dolly', 'records', 'mails', 'communications', 'rooms', 'containers', 'shelves', 'slip_records'));
    }





    public function edit(Dolly $dolly)
    {
        $categories = Dolly::all()->pluck('category');
        return view('dollies.edit', compact('dolly', 'categories'));
    }






    public function update(Request $request, Dolly $dolly)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'category' => 'required|exists:dollies,category',
        ]);

        $validatedData['is_public'] = false;
        $validatedData['created_by'] = Auth::user()->getAuthIdentifier;
        $validatedData['owner_organisation_id'] = Auth::user()->current_organisation_id;

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






    public function apiList(Request $request)
    {
        $query = Dolly::where('category', 'mail')
            ->where('owner_organisation_id', Auth::user()->current_organisation_id);

        if ($request->has('q') && $request->q) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $dollies = $query->get();
        return response()->json($dollies);
    }




    public function apiCreate(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validatedData['is_public'] = false;
        $validatedData['created_by'] = Auth::id();
        $validatedData['owner_organisation_id'] = Auth::user()->current_organisation_id;
        $validatedData['category'] = 'mail';

        $dolly = Dolly::create($validatedData);

        return response()->json($dolly);
    }


}


