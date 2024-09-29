<?php

namespace App\Http\Controllers;
use App\Models\RecordContainer;
use App\Models\Container;
use App\Models\Record;
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

        $record = Record::with('containers', 'children', 'attachments')->findOrFail($validatedData['r_id']);

        return view('records.show', compact('record'))->with('success',"Archives ajoutées dans la boite");

    }





    public function edit($recordId, $containerId)
    {
        $recordContainer = RecordContainer::where('record_id', $recordId)
            ->where('container_id', $containerId)
            ->firstOrFail();

        return view('record_containers.edit', compact('recordContainer'));
    }





    public function update(Request $request, $recordId, $containerId)
    {
        $validatedData = $request->validate([
            'description' => 'nullable|string|max:100',
            'creator_id' => 'required|exists:users,id',
        ]);

        $recordContainer = RecordContainer::where('record_id', $recordId)
            ->where('container_id', $containerId)
            ->firstOrFail();

        $recordContainer->update([
            'description' => $validatedData['description'],
            'creator_id' => $validatedData['creator_id'],
        ]);

    }


    public function destroy(Request $request)
    {


        $validatedData = $request->validate([
            'c_id' => 'required|exists:containers,id',
            'r_id' => 'required|exists:records,id',
        ]);

        $record = Record::findOrFail($validatedData['r_id']);

        $record->containers()->detach($validatedData['c_id']);

        return view('records.show', compact('record'))->with('success',"documents retirés de la boites d'archives");
    }


}
