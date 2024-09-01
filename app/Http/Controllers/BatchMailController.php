<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchMail;
use App\Models\Mail;
use App\Models\MailbatchTransaction;
use Illuminate\Http\Request;

class BatchMailController extends Controller
{
    public function index(Batch $batch)
    {
        $batchMails = BatchMail::where('batch_id', $batch->id)->get();
        $batchMails->load('mails');
        dd($batchMails);
        return view('batch.mail.index', compact('batchMails', 'batch'));
    }



    public function create(Batch $batch)
    {

        $mails = Mail::all();
        return view('batch.mail.create', compact('batch', 'mails'));
    }



    public function store(Request $request, Batch $batch)
    {
        $batchMail = new BatchMail([
            'mail_id' => $request->mail_id,
            'insert_date' => now(),
        ]);

        $batch->batchMails()->save($batchMail);

        return redirect()->route('batch.mail.index', $batch)
            ->with('success', 'Courriel de lot créé avec succès.');
    }


    public function edit(Batch $batch, BatchMail $batchMail)
    {
        $mails = Mail::all();
        return view('batch.mail.edit', compact('batch', 'batchMail', 'mails'));
    }




    public function update(Request $request, Batch $batch, BatchMail $batchMail)
    {
        $validatedData = $request->validate([
            'mail_id' => 'required|exists:mails,id'
        ]);

        $validatedData['insert_date'] = now();
        $batchMail->update($validatedData);

        return redirect()->route('batch.mail.index', $batch)->with('success', 'Batch mail updated successfully.');
    }



    public function destroy(Batch $batch, BatchMail $batchMail)
    {
        $batchMail->delete();

        return redirect()->route('batch.mail.index', $batch)->with('success', 'Batch mail deleted successfully.');
    }


}
