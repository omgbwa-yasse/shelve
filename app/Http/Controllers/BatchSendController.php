<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\BatchTransaction;
use App\Models\Organisation;
use App\Models\user;

class BatchSendController extends Controller
{
    public function index()
    {
        $organisation = User::with('organisations');
        $batchTransactions = Batchtransaction::whereIn('organisation_send_id',
            auth()->user()->organisations->pluck('id'))->get();
        return view('batch.send.index', compact('batchTransactions'));
    }


    public function batches_send()
    {
        $batchTransactions = BatchTransaction::with(['batch', 'organisationSend', 'organisationReceived'])->get();
        return view('batch.send.send', compact('batchTransactions'));
    }

    public function create()
    {
        $batches = Batch::all();
        $organisations = Organisation::all();
        return view('batch.send.create', compact('batches', 'organisations'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'batch_id' => 'required|integer',
            'organisation_send_id' => 'required|integer',
            'organisation_received_id' => 'required|integer',
        ]);
        BatchTransaction::create($validatedData);
        return redirect()->route('batch-send.index')->with('success', 'Batch transaction created successfully.');
    }

    public function edit(INT $id)
    {
        $batches = Batch::all();
        $organisations = Organisation::all();
        $batchTransaction = Batchtransaction::whereIn('organisation_send_id',
            auth()->user()->organisations->pluck('id'))->get();
        return view('batch.send.edit', compact('batchTransaction', 'batches', 'organisations'));
    }

    public function update(Request $request, BatchTransaction $batchTransaction)
    {
        $validatedData = $request->validate([
            'batch_id' => 'required|integer',
            'organisation_send_id' => 'required|integer',
            'organisation_received_id' => 'required|integer',
        ]);

        $batchTransaction->update($validatedData);
        return redirect()->route('batch-send.index')->with('success', 'Batch transaction updated successfully.');
    }


    public function destroy(BatchTransaction $batchTransaction)
    {
        $batchTransaction->delete();
        return redirect()->route('batch-send.index')->with('success', 'Batch transaction deleted successfully.');
    }
}
