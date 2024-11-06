<?php

namespace App\Http\Controllers;

use App\Models\MailAction;
use Illuminate\Http\Request;

class MailActionController extends Controller
{
    public function index()
    {
        $mailActions = MailAction::all();
        return view('mails.actions.index', compact('mailActions'));
    }

    public function create()
    {
        return view('mails.actions.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:mail_actions|max:100',
            'duration' => 'required|integer',
            'to_return' => 'nullable|boolean',
            'description' => 'required',
        ]);

        MailAction::create($validatedData);

        return redirect()->route('mail-action.index')->with('success', 'Mail action created successfully.');
    }

    public function edit(MailAction $mailAction)
    {
        return view('mails.actions.edit', compact('mailAction'));
    }

    public function update(Request $request, MailAction $mailAction)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:mail_actions,name,' . $mailAction->id . '|max:100',
            'duration' => 'required|integer',
            'to_return' => 'nullable|boolean',
            'description' => 'required',
        ]);

        $mailAction->update($validatedData);

        return redirect()->route('mail-action.index')->with('success', 'Mail action updated successfully.');
    }

    public function destroy(MailAction $mailAction)
    {
        $mailAction->delete();

        return redirect()->route('mail-action.index')->with('success', 'Mail action deleted successfully.');
    }
}
