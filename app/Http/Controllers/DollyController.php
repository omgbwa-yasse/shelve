<?php

namespace App\Http\Controllers;

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
        $dolly->load('type');
        return view('dollies.show', compact('dolly'));
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
}


