<?php

namespace App\Http\Controllers;
use App\Models\Record;
use App\Models\RecordSupport;
use App\Models\RecordStatus;
use App\Models\Container;
use App\Models\ContainerStatus;
use App\Models\Classification;
use App\Models\RecordLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Monolog\Level;

class RecordController extends Controller
{

    // Display a listing of the resource.
    public function index()
    {
        $records = Record::all();
        return view('records.index', compact('records'));
    }

    // Show the form for creating a new resource.
    public function create()
    {
        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $levels = RecordLevel::all();
        $classifications = Classification::all();
        $containers = Container::all();
        $users = User::all();
        return view('records.create', compact('statuses', 'supports', 'classifications', 'containers', 'users', 'levels'));
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $request->validate([
            'reference' => 'required',
            'name' => 'required',
            'date_format' => 'required',
            'date_start' => 'required',
            'date_end' => 'required',
            'date_exact' => 'required',
            'description' => 'required',
            'level_id' => 'required',
            'status_id' => 'required',
            'support_id' => 'required',
            'classification_id' => 'required',
            'parent_id' => 'nullable',
            'container_id' => 'required',
            'transfer_id' => 'nullable',
            'user_id' => 'required',
        ]);

        $record = new Record();
        $record->fill($request->all());
        $record->user_id = Auth::id();
        $record->save();

        return redirect()->route('records.index')
                        ->with('success','Record created successfully.');
    }

    // Display the specified resource.
    public function show(Record $record)
    {
        return view('records.show',compact('record'));
    }

    // Show the form for editing the specified resource.
    public function edit(Record $record)
    {
        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $classifications = Classification::all();
        $containers = Container::all();
        $users = User::all();
        return view('records.edit',compact('record', 'statuses', 'supports', 'classifications', 'containers', 'users'));
    }

    // Update the specified resource in storage.
    public function update(Request $request, Record $record)
    {
        $request->validate([
            'reference' => 'required',
            'name' => 'required',
            'date_format' => 'required',
            'date_start' => 'required',
            'date_end' => 'required',
            'date_exact' => 'required',
            'description' => 'required',
            'level_id' => 'required',
            'status_id' => 'required',
            'support_id' => 'required',
            'classification_id' => 'required',
            'parent_id' => 'nullable',
            'container_id' => 'required',
            'transfer_id' => 'nullable',
            'user_id' => 'required',
        ]);

        $record->update($request->all());

        return redirect()->route('records.index')
                        ->with('success','Record updated successfully');
    }

    // Remove the specified resource from storage.
    public function destroy(Record $record)
    {
        $record->delete();

        return redirect()->route('records.index')
                        ->with('success','Record deleted successfully');
    }
}


