<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Organisation;
use App\Models\BatchTransaction;
use App\Models\MailTransaction;
use Illuminate\Support\Facades\DB;


class BatchReceivedController extends Controller
{


    public function index()
    {
        $batchTransactions = BatchTransaction::with(['batch', 'organisationSend', 'organisationReceived'])
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('batch_transactions')
                    ->groupBy('batch_id');
            })
            ->where('organisation_received_id', auth()->user()->currentOrganisation->id)
            ->paginate(10);

        return view('batch.received.index', compact('batchTransactions'));
    }



    public function logs()
    {
        $batchTransactions = Batchtransaction::where('organisation_received_id',
            auth()->user()->currentOrganisation->id)
            ->latest()
            ->paginate(10);
        return view('batch.received.index', compact('batchTransactions'));
    }



    public function create()
    {
        // Pour recevoir : afficher TOUS les parapheurs (recherche par code)
        $batches = Batch::with('organisationHolder')->get();

        $organisations = Organisation::whereNot('id', auth()->user()->currentOrganisation->id)->get();
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
        ]);

        $validatedData['organisation_received_id'] = auth()->user()->currentOrganisation->id;

        $batchTransaction = BatchTransaction::create($validatedData);
        $batch = $batchTransaction->batch;

        $i = 1;
        foreach($batch->mails as $data){
            $mail = [];
            $mail['code'] = $batch->code . $i;
            $mail['date_creation'] = now();
            $mail['mail_id'] = $data->id;
            $mail['user_send_id'] = auth()->user()->id;
            $mail['organisation_send_id'] = auth()->user()->currentOrganisation->id;
            $mail['user_received_id'] = null; // Can be updated later
            $mail['organisation_received_id'] = $validatedData['organisation_received_id'];
            $mail['document_type_id'] = $data->document_type_id;
            $mail['action_id'] = NULL;
            $mail['to_return'] = $data->to_return;
            $mail['description'] = $data->to_return;
            $mail['batch_id'] = $batch->id;
            $i++;
            MailTransaction::create($mail);
        }

        return redirect()->route('batch-received.index')->with('success', 'Batch transaction created successfully.');
    }






    public function edit(INT $id)
    {
        $batchTransaction = BatchTransaction::findOrFail($id);


         $batches = Batch::whereHas('transactions', function($query) {
            $query->where('organisation_received_id', auth()->user()->currentOrganisation->id)
                  ->where('id', function($subQuery) {
                      $subQuery->selectRaw('MAX(id)')
                               ->from('batch_transactions')
                               ->whereColumn('batch_id', 'batch_transactions.batch_id');
                  });
        })->get();


        $organisations = Organisation::whereNot('id', auth()->user()->currentOrganisation->id)->get();
        return view('batch.received.edit', compact('batchTransaction', 'batches','organisations'));
    }

    public function update(Request $request, BatchTransaction $batchTransaction)
    {
        $validatedData = $request->validate([
            'batch_id' => 'required|integer',
            'organisation_send_id' => 'required|integer',
        ]);

        $validatedData['organisation_received_id'] = auth()->user()->currentOrganisation->id;

        $batchTransaction->update($validatedData);

        return redirect()->route('batch-received.index')->with('success', 'Batch transaction updated successfully.');
    }

    public function destroy(BatchTransaction $batchTransaction)
    {
        $batchTransaction->delete();

        return redirect()->route('batch-received.index')->with('success', 'Batch transaction deleted successfully.');
    }
}
