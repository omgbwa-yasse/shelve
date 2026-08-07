<?php

namespace App\Http\Controllers;

use App\Models\MailTypology;
use App\Models\activity;
use Illuminate\Http\Request;

class MailTypologyController extends Controller
{

    public function index()
    {
        $mailTypologies = MailTypology::paginate(10);
        $mailTypologies->load(['activity','mails']);

        return view('mails.typologies.index', compact('mailTypologies'));
    }


    public function create()
    {
        $classes = activity::all();

        return view('mails.typologies.create', compact('classes'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:mail_typologies|max:50',
            'description' => 'nullable|max:100',
            'activity_id' => 'required|exists:activities,id',
        ]);

        MailTypology::create($request->all());

        return redirect()->route('mail-typology.index')
            ->with('success', 'Mail typology created successfully.');
    }





    public function show(MailTypology $mailTypology)
    {
        return view('mails.typologies.show', compact('mailTypology'));
    }





    public function edit(MailTypology $mailTypology)
    {
        $classes = activity::all();

        return view('mails.typologies.edit', compact('mailTypology', 'classes'));
    }





    public function update(Request $request, MailTypology $mailTypology)
    {
        $request->validate([
            'name' => 'required|unique:mail_typologies,name,'.$mailTypology->id.'|max:50',
            'description' => 'nullable|max:100',
            'activity_id' => 'required|exists:activities,id',
        ]);

        $mailTypology->update($request->all());

        return redirect()->route('mail-typology.index')
            ->with('success', 'Mail typology updated successfully.');
    }




    public function destroy(MailTypology $mailTypology)
    {
        $mailTypology->delete();

        return redirect()->route('mail-typology.index')
            ->with('success', 'Mail typology deleted successfully.');
    }
}
