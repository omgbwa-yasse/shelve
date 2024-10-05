<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\BatchTransaction;
use App\Models\MailTransaction;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;
use App\Models\user;

class BatchSendController extends Controller
{
    public function index()
    {
        $batchTransactions = Batchtransaction::where('organisation_send_id',
            auth()->user()->currentOrganisation->id??'')
            ->latest()
            ->paginate(10);
        return view('batch.send.index', compact('batchTransactions'));
    }
    public function show(BatchTransaction $batchTransaction)
    {
        // Assurez-vous que l'utilisateur a les droits de voir cette transaction
        if ($batchTransaction->organisation_send_id !== auth()->user()->currentOrganisation->id) {
            return redirect()->route('batch-send.index')->with('error', 'Unauthorized access.');
        }

        // Récupérez les détails de la transaction de batch
        $batchTransaction->load(['batch', 'organisationSend', 'organisationReceived']);

        return view('batch.send.show', compact('batchTransaction'));
    }





    public function last()
    {
        $latestBatchTransactions = BatchTransaction::with(['batch', 'organisationSend', 'organisationReceived'])
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('batch_transactions')
                    ->groupBy('batch_id');
            })
            ->where('organisation_send_id', auth()->user()->currentOrganisation->id)
            ->paginate(10);

        return view('batch.received.index', compact('latestBatchTransactions'));
    }





    public function logs()
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
            'organisation_received_id' => 'required|integer',
            'user_received_id' => 'required|integer', // Assuming this is a required field
        ]);

        $validatedData['organisation_send_id'] = auth()->user()->currentOrganisation->id;

        $batch = BatchTransaction::create($validatedData);

        $i = 1;
        foreach($batch->mails as $data){
            $mail = [];
            $mail['code'] = $batch->code . $i;
            $mail['date_creation'] = now();
            $mail['mail_id'] = $data->id;
            $mail['user_send_id'] = auth()->user()->id;
            $mail['organisation_send_id'] = auth()->user()->currentOrganisation->id;
            $mail['user_received_id'] = $validatedData['user_received_id'];
            $mail['organisation_received_id'] = $validatedData['organisation_received_id'];
            $mail['document_type_id'] = $data->document_type_id;
            $mail['action_id'] = NULL;
            $mail['to_return'] = $data->to_return;
            $mail['description'] = $data->to_return;
            $mail['batch_id'] = $batch->id;
            $i++;
            MailTransaction::create($mail);
        }

        return redirect()->route('batch-send.index')->with('success', 'Batch transaction created successfully.');
    }




    public function edit(INT $id)
    {
        $batches = Batch::all();
        $organisations = Organisation::all();
        $batchTransaction = Batchtransaction::where('organisation_send_id',
            auth()->user()->currentOrganisation->id);
        return view('batch.send.edit', compact('batchTransaction', 'batches', 'organisations'));
    }



    public function update(Request $request, BatchTransaction $batchTransaction)
    {
        $validatedData = $request->validate([
            'batch_id' => 'required|integer',
            'organisation_received_id' => 'required|integer',
        ]);

        $validatedData['organisation_send_id'] = auth()->user()->currentOrganisation->id;

        $batchTransaction->update($validatedData);
        return redirect()->route('batch-send.index')->with('success', 'Batch transaction updated successfully.');
    }



    public function destroy(BatchTransaction $batchTransaction)
    {
        $batchTransaction->delete();
        return redirect()->route('batch-send.index')->with('success', 'Batch transaction deleted successfully.');
    }
}
