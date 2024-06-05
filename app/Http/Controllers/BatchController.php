<?php

namespace App\Http\Controllers;

use App\Models\MailBatch;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index()
    {
        $mailbatches = MailBatch::all();
        return view('mails.batch.index', compact('mailbatches'));
    }

    public function create()
    {
        return view('mails.batch.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|max:10',
            'name' => 'required|max:100',
        ]);

        MailBatch::create($request->all());

        return redirect()->route('mails.batch.index')
            ->with('success', 'Mail batch created successfully.');
    }



    public function show(MailBatch $mailbatch)
    {
        return view('mails.batch.show', compact('mailbatch'));
    }




    public function edit(MailBatch $mailbatch)
    {
        return view('mails.batch.edit', compact('mailbatch'));
    }



    public function update(Request $request, MailBatch $mailbatch)
    {
        $request->validate([
            'code' => 'nullable|max:10',
            'name' => 'required|max:100',
        ]);

        $mailbatch->update($request->all());

        return redirect()->route('batch.index')
            ->with('success', 'Mail batch updated successfully.');
    }




    public function destroy(MailBatch $mailbatch)
    {
        $mailbatch->delete();

        return redirect()->route('batch.index')
            ->with('success', 'Mail batch deleted successfully.');
    }
}
