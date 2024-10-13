<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchMail;
use App\Models\Mail;
use App\Models\MailbatchTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class BatchMailController extends Controller
{
    public function index(Batch $batch)
    {
        $batchMails = BatchMail::where('batch_id', $batch->id)->get();
        $batchMails->load('mail');

        return view('batch.mail.index', compact('batchMails', 'batch'));
    }



    public function create(Batch $batch)
    {

        $mails = Mail::all();
        return view('batch.mail.create', compact('batch', 'mails'));
    }



    public function store(Request $request, Batch $batch)
    {
        $validatedData = $request->validate([
            'mail_id' => 'required|exists:mails,id'
        ]);

        $batchMail = new BatchMail([
            'mail_id' => $validatedData['mail_id'],
            'insert_date' => now(),
            'batch_id' => $batch->id,
        ]);

        $batchMail->save();

        return redirect()->route('batch.show', $batch->id)
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


    public function destroy(Batch $batch, int $id)
    {
        $mail = $batch->mails()->find($id);

        if (!$mail) {
            return redirect()->route('batch.show', $batch->id)->with('error', 'Mail not found in this batch.');
        }


        $batch->mails()->detach($id);

        return redirect()->route('batch.show', $batch->id)->with('success', 'Mail and its attachments removed from batch successfully.');
    }

}
