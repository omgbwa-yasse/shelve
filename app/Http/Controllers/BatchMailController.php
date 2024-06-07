<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatchMail;
use App\Models\Mail;

class BatchMailController extends Controller
{

    public function index()
    {
        $batchMails = BatchMail::with(['batch', 'mail'])->get();
        return view('batch_mails.index', compact('batchMails'));
    }



    public function create()
    {
        $batches = BatchMail::all();
        $mails = Mail::all();
        return view('batch_mails.create', compact('batches', 'mails'));
    }



    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'batch_id' => 'required|integer',
            'mail_id' => 'required|integer',
            'insertion_date' => 'required|date',
            'exit_date' => 'nullable|date'
        ]);

        BatchMail::create($validatedData);

        return redirect()->route('batch_mails.index')->with('success', 'Batch mail created successfully.');
    }



    public function edit(BatchMail $batchMail)
    {
        $batches = BatchMail::all();
        $mails = Mail::all();
        return view('batch_mails.edit', compact('batchMail', 'batches', 'mails'));
    }



    public function update(Request $request, BatchMail $batchMail)
    {
        $validatedData = $request->validate([
            'batch_id' => 'required|integer',
            'mail_id' => 'required|integer',
            'insertion_date' => 'required|date',
            'exit_date' => 'nullable|date'
        ]);

        $batchMail->update($validatedData);

        return redirect()->route('batch_mails.index')->with('success', 'Batch mail updated successfully.');
    }



    public function destroy(BatchMail $batchMail)
    {
        $batchMail->delete();

        return redirect()->route('batch_mails.index')->with('success', 'Batch mail deleted successfully.');
    }
}
