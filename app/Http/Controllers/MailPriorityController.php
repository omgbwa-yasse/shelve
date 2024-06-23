<?php

namespace App\Http\Controllers;
use App\models\MailPriority;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MailPriorityController extends Controller
{

    public function index()
    {
        $mailPriorities = MailPriority::all();
        return view('mail-priorities.index', compact('mailPriorities'));
    }



    public function create()
    {
        return view('mail-priorities.create');
    }



    public function store(Request $request)
    {
        MailPriority::create($request->all());
        return redirect()->route('mail-priorities.index')
                        ->with('success','Mail Priority created successfully');
    }



    public function show(MailPriority $mailPriority)
    {
        return view('mail-priorities.show', compact('mailPriority'));
    }



    public function edit(MailPriority $mailPriority)
    {
        return view('mail-priorities.edit', compact('mailPriority'));
    }



    public function update(Request $request, MailPriority $mailPriority)
    {
        $mailPriority->update($request->all());
        return redirect()->route('mail-priorities.index')
                        ->with('success','Mail Priority updated successfully');
    }



    public function destroy(MailPriority $mailPriority)
    {
        $mailPriority->delete();
        return redirect()->route('mail-priorities.index')
                        ->with('success','Mail Priority deleted successfully');
    }
}
