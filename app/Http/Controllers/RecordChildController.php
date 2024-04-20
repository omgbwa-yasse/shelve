<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Http\Request;

class RecordChildController extends Controller
{
    public function index(Record $record)
    {
        $children = $record->children;
        return view('records.children.index', compact('record', 'children'));
    }

    public function create(Record $record)
    {
        return view('records.children.create', compact('record'));
    }

    public function store(Request $request, Record $record)
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

        $child = new Record([
            'parent_id' => $record->id,
            'reference' => $request->input('reference'),
            'name' => $request->input('name'),
            'date_format' => $request->input('date_format'),
            'date_start' => $request->input('date_start'),
            'date_end' => $request->input('date_end'),
            'date_exact' => $request->input('date_exact'),
            'description' => $request->input('description'),
            'level_id' => $request->input('level_id'),
            'status_id' => $request->input('status_id'),
            'support_id' => $request->input('support_id'),
            'classification_id' => $request->input('classification_id'),
            'parent_id' => $request->input('parent_id'),
            'container_id' => $request->input('container_id'),
            'transfer_id' => $request->input('transfer_id'),
            'user_id' => $request->input('user_id'),
        ]);

        $record->children()->save($child);

        return redirect()->route('records.children.index', $record)->with('success', 'Child record created successfully.');
    }

    public function edit(Record $record, Record $child)
    {
        return view('records.children.edit', compact('record', 'child'));
    }

    public function update(Request $request, Record $record, Record $child)
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

        $child->update([
            'reference' => $request->input('reference'),
            'name' => $request->input('name'),
            'date_format' => $request->input('date_format'),
            'date_start' => $request->input('date_start'),
            'date_end' => $request->input('date_end'),
            'date_exact' => $request->input('date_exact'),
            'description' => $request->input('description'),
            'level_id' => $request->input('level_id'),
            'status_id' => $request->input('status_id'),
            'support_id' => $request->input('support_id'),
            'classification_id' => $request->input('classification_id'),
            'parent_id' => $request->input('parent_id'),
            'container_id' => $request->input('container_id'),
            'transfer_id' => $request->input('transfer_id'),
            'user_id' => $request->input('user_id'),
        ]);

        return redirect()->route('records.children.index', $record)->with('success', 'Child record updated successfully.');
    }

    public function destroy(Record $record, Record $child)
    {
        $child->delete();

        return redirect()->route('records.children.index', $record)->with('success', 'Child record deleted successfully.');
    }
}
