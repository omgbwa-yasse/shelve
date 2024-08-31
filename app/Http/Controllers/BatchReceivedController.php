<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\User;
use App\Models\BatchTransaction;
use App\Models\Organisation;

class BatchReceivedController extends Controller
{
    public function index()
    {
        $organisation = User::with('organisations');
        $batchTransactions = Batchtransaction::where('organisation_received_id', auth()->user()->organisation->id);
        return view('batch.received.index', compact('batchTransactions'));
    }


    public function batches_received()
    {
        $batchTransactions = BatchTransaction::with(['batch', 'organisationSend', 'organisationReceived'])->get();
        return view('batch.received.index', compact('batchTransactions'));
    }


    public function create()
    {
        $batches = Batch::all();
        $organisations = Organisation::all();
        return view('batch.received.create', compact('batches', 'organisations'));
    }



    public function show(INT $id)
    {
        $batchTransaction = BatchTransaction::findOrFail($id);
        $organisations = Organisation::all();
        return view('batch.received.show', compact('batchTransaction', 'organisations'));
    }




    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'batch_id' => 'required|integer',
            'organisation_send_id' => 'required|integer',
            'organisation_received_id' => 'required|integer',
        ]);

        BatchTransaction::create($validatedData);
        return redirect()->route('batch-received.index')->with('success', 'Batch transaction created successfully.');
    }

    public function edit(INT $id)
    {
        $batchTransaction = BatchTransaction::findOrFail($id);
        $batches = Batch::all();
        $organisations = Organisation::all();
        return view('batch.received.edit', compact('batchTransaction', 'batches','organisations'));
    }

    public function update(Request $request, BatchTransaction $batchTransaction)
    {
        $validatedData = $request->validate([
            'batch_id' => 'required|integer',
            'organisation_send_id' => 'required|integer',
            'organisation_received_id' => 'required|integer',
        ]);

        $batchTransaction->update($validatedData);

        return redirect()->route('batch-received.index')->with('success', 'Batch transaction updated successfully.');
    }

    public function destroy(BatchTransaction $batchTransaction)
    {
        $batchTransaction->delete();

        return redirect()->route('batch-received.index')->with('success', 'Batch transaction deleted successfully.');
    }
}
