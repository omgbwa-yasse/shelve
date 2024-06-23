<?php

namespace App\Http\Controllers;

use App\models\MailTypology;
use App\models\TypologyCategory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MailTypologyController extends Controller
{

    public function index()
    {
        $mailTypologies = MailTypology::with('typologyCategory')->get();
        return view('mails.typology.index', compact('mailTypologies'));
    }


    public function create()
    {
        $typologyCategories = TypologyCategory::all();
        return view('mails.typology.create', compact('typologyCategories'));
    }

    public function store(Request $request)
    {
        $mailTypology = MailTypology::create($request->all());
        return redirect()->route('mail_typologies.index')
                        ->with('success','Mail typology created successfully');
    }


    public function show(MailTypology $mailTypology)
    {
        return view('mails.typology.show', compact('mailTypology'));
    }

    public function edit(MailTypology $mailTypology)
    {
        $typologyCategories = TypologyCategory::all();
        return view('mails.typology.edit', compact('mailTypology', 'typologyCategories'));
    }


    public function update(Request $request, MailTypology $mailTypology)
    {
        $mailTypology->update($request->all());
        return redirect()->route('mail_typologies.index')
                        ->with('success','Mail typology updated successfully');
    }


    public function destroy(MailTypology $mailTypology)
    {
        $mailTypology->delete();
        return redirect()->route('mail_typologies.index')
                        ->with('success','Mail typology deleted successfully');
    }
}
