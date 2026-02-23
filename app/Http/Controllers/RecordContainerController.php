<?php

namespace App\Http\Controllers;
use App\Models\RecordContainer;
use App\Models\Container;
use App\Models\RecordPhysical;
use Illuminate\Http\Request;

class RecordContainerController extends Controller
{

    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'code' => 'required|exists:containers,code',
            'r_id' => 'required|exists:records,id',
            'description' => 'nullable|string|max:100',

        ]);

        $container = Container::where('code', $validatedData['code'])->firstOrFail();

        $recordContainer = new RecordContainer([
            'record_id' => $validatedData['r_id'],
            'container_id' => $container->id,
            'description' => $validatedData['description'],
            'creator_id' => auth()->user()->id,
        ]);



        $recordContainer->save();

        $record = RecordPhysical::with('containers', 'children', 'attachments')->findOrFail($validatedData['r_id']);

        return view('records.show', compact('record'))->with('success',"Archives ajoutées dans la boite");

    }


    public function destroy(Request $request)
    {


        $validatedData = $request->validate([
            'c_id' => 'required|exists:containers,id',
            'r_id' => 'required|exists:records,id',
        ]);

        $record = RecordPhysical::findOrFail($validatedData['r_id']);

        $record->containers()->detach($validatedData['c_id']);

        return view('records.show', compact('record'))->with('success',"documents retirés de la boites d'archives");
    }


}
