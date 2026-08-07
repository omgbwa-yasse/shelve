<?php

namespace App\Http\Controllers;

use App\Models\MailPriority;
use Illuminate\Http\Request;

class MailPriorityController extends Controller
{
    public function index()
    {
        $mailPriorities = MailPriority::all();
        return view('mails.priorities.index', compact('mailPriorities'));
    }



    public function create()
    {
        return view('mails.priorities.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:mail_priorities|max:50', // Added validation rules
            'duration' => 'required|integer',
        ]);

        MailPriority::create($request->all());

        return redirect()->route('mail-priority.index')
                        ->with('success','Mail Priority created successfully');
    }



    public function show(MailPriority $mailPriority)
    {
        return view('mails.priorities.show', compact('mailPriority'));
    }



    public function edit(MailPriority $mailPriority)
    {
        return view('mails.priorities.edit', compact('mailPriority'));
    }



    public function update(Request $request, MailPriority $mailPriority)
    {
        $request->validate([
            'name' => 'required|unique:mail_priorities,name,' . $mailPriority->id . '|max:50', // Added validation rules
            'duration' => 'required|integer',
        ]);

        $mailPriority->update($request->all());

        return redirect()->route('mail-priority.index')
                        ->with('success','Mail Priority updated successfully');
    }



    public function destroy(MailPriority $mailPriority)
    {
        $mailPriority->delete();

        return redirect()->route('mail-priority.index')
                        ->with('success','Mail Priority deleted successfully');
    }


}
