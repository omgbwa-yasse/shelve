<?php

namespace App\Http\Controllers;

use App\Models\MailBatch;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index()
    {
        $mailBatches = MailBatch::all(); // Correction de la casse camelCase
        return view('batch.index', compact('mailBatches'));
    }



    public function create()
    {
        return view('batch.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'nullable|unique:mail_batches|max:10',
            'name' => 'required|max:100',
        ]);

        MailBatch::create($validatedData);

        return redirect()->route('batch.index')->with('success', 'Mail batch created successfully.');
    }




    public function show(MailBatch $mailBatch)
    {
        return view('batch.show', compact('mailBatch'));
    }




    public function edit(MailBatch $mailBatch)
    {
        return view('batch.edit', compact('mailBatch'));
    }




    public function update(Request $request, MailBatch $mailBatch)
    {
        $validatedData = $request->validate([
            'code' => 'nullable|unique:mail_batches,code,' . $mailBatch->id . '|max:10',
            'name' => 'required|max:100',
        ]);

        $mailBatch->update($validatedData);

        return redirect()->route('batch.index')->with('success', 'Mail batch updated successfully.');
    }




    public function destroy(MailBatch $mailBatch)
    {
        $mailBatch->delete();

        return redirect()->route('batch.index')->with('success', 'Mail batch deleted successfully.');
    }
}
