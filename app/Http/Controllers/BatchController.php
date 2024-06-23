<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index()
    {
        $mailBatches = Batch::all(); // Correction de la casse camelCase
        return view('batch.index', compact('mailBatches'));
    }



    public function create()
    {
        return view('batch.create');
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'nullable|unique:batches|max:10',
            'name' => 'required|max:100',
        ]);

        Batch::create($validatedData);

        return redirect()->route('batch.index')->with('success', 'Mail batch created successfully.');
    }




    public function show(INT $id)
    {
        $mailBatch = Batch::findOrFail($id);
        return view('batch.show', compact('mailBatch'));
    }




    public function edit(INT $id)
    {
        $mailBatch = Batch::findOrFail($id);
        return view('batch.edit', compact('mailBatch'));
    }




    public function update(Request $request, INT $id)
    {
        $mailBatch = Batch::findOrFail($id);
        $validatedData = $request->validate([
            'code' => 'nullable|unique:batches,code,' . $mailBatch->id . '|max:10',
            'name' => 'required|max:100',
        ]);
        $mailBatch->update($validatedData);
        return redirect()->route('batch.index')->with('success', 'Mail batch updated successfully.');
    }




    public function destroy(Batch $mailBatch)
    {
        $mailBatch->delete();

        return redirect()->route('batch.index')->with('success', 'Mail batch deleted successfully.');
    }
}
