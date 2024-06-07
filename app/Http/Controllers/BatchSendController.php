<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\BatchTransaction;
use App\Models\Organisation;

class BatchSendController extends Controller
{
    public function index()
    {
        $batchTransactions = BatchTransaction::with(['batch', 'organisationSend', 'organisationReceived'])->get();
        return view('batch_transactions.index', compact('batchTransactions'));
    }

    public function create()
    {
        $batches = Batch::all();
        $organisations = Organisation::all();
        return view('batch_transactions.create', compact('batches', 'organisations'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'batch_id' => 'required|integer',
            'organisation_send_id' => 'required|integer',
            'organisation_received_id' => 'required|integer',
        ]);

        BatchTransaction::create($validatedData);

        return redirect()->route('batch_transactions.index')->with('success', 'Batch transaction created successfully.');
    }

    public function edit(BatchTransaction $batchTransaction)
    {
        $batches = Batch::all();
        $organisations = Organisation::all();
        return view('batch_transactions.edit', compact('batchTransaction', 'batches', 'organisations'));
    }

    public function update(Request $request, BatchTransaction $batchTransaction)
    {
        $validatedData = $request->validate([
            'batch_id' => 'required|integer',
            'organisation_send_id' => 'required|integer',
            'organisation_received_id' => 'required|integer',
        ]);

        $batchTransaction->update($validatedData);

        return redirect()->route('batch_transactions.index')->with('success', 'Batch transaction updated successfully.');
    }

    public function destroy(BatchTransaction $batchTransaction)
    {
        $batchTransaction->delete();

        return redirect()->route('batch_transactions.index')->with('success', 'Batch transaction deleted successfully.');
    }
}
