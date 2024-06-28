<?php

namespace App\Http\Controllers;
use App\models\RecordStatus;
use Illuminate\Http\Request;

class RecordStatusController extends Controller
{

    public function index()
    {
        $recordStatuses = RecordStatus::all();
        return view('records.statuses.index', compact('recordStatuses'));
    }



    public function create()
    {
        return view('records.statuses.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:record_status|max:100',
            'description' => 'nullable',
        ]);

        RecordStatus::create($request->all());

        return redirect()->route('record-statuses.index')
            ->with('success', 'Statut enregistré avec succès.');
    }




    public function show($id)
    {
        $recordStatus = RecordStatus::findOrFail($id);
        return view('records.statuses.show', compact('recordStatus'));
    }




    public function edit($id)
    {
        $recordStatus = RecordStatus::findOrFail($id);
        return view('records.statuses.edit', compact('recordStatus'));
    }




    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:record_status,name,'.$id.'|max:100',
            'description' => 'nullable',
        ]);

        $recordStatus = RecordStatus::findOrFail($id);
        $recordStatus->update($request->all());

        return redirect()->route('record-statuses.index')
            ->with('success', 'Statut mis à jour avec succès.');
    }




    public function destroy($id)
    {
        $recordStatus = RecordStatus::findOrFail($id);
        $recordStatus->delete();

        return redirect()->route('record-statuses.index')
            ->with('success', 'Statut supprimé avec succès.');
    }
}

