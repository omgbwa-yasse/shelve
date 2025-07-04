<?php

namespace App\Http\Controllers;

use App\Models\ExternalContact;
use App\Models\ExternalOrganization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExternalContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = ExternalContact::with('organization')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15);

        return view('external.contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organizations = ExternalOrganization::orderBy('name')->pluck('name', 'id');
        return view('external.contacts.create', compact('organizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'position' => 'nullable|string|max:255',
            'external_organization_id' => 'nullable|exists:external_organizations,id',
            'is_primary_contact' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Si ce contact est défini comme contact principal, mettre à jour les autres contacts de la même organisation
        if ($request->filled('external_organization_id') && $request->boolean('is_primary_contact')) {
            ExternalContact::where('external_organization_id', $request->external_organization_id)
                ->update(['is_primary_contact' => false]);
        }

        $contact = ExternalContact::create($request->all());

        return redirect()->route('external.contacts.show', $contact)
            ->with('success', 'Contact externe créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contact = ExternalContact::with('organization')->findOrFail($id);
        $sentMails = $contact->sentMails()->with('recipient')->take(5)->get();
        $receivedMails = $contact->receivedMails()->with('sender')->take(5)->get();

        return view('external.contacts.show', compact('contact', 'sentMails', 'receivedMails'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $contact = ExternalContact::findOrFail($id);
        $organizations = ExternalOrganization::orderBy('name')->pluck('name', 'id');

        return view('external.contacts.edit', compact('contact', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $contact = ExternalContact::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'position' => 'nullable|string|max:255',
            'external_organization_id' => 'nullable|exists:external_organizations,id',
            'is_primary_contact' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Si ce contact est défini comme contact principal, mettre à jour les autres contacts de la même organisation
        if ($request->filled('external_organization_id') && $request->boolean('is_primary_contact')) {
            ExternalContact::where('external_organization_id', $request->external_organization_id)
                ->where('id', '!=', $id)
                ->update(['is_primary_contact' => false]);
        }

        $contact->update($request->all());

        return redirect()->route('external.contacts.show', $contact)
            ->with('success', 'Contact externe mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $contact = ExternalContact::findOrFail($id);

        // Vérifier si le contact est utilisé dans des courriers
        $mailsCount = $contact->sentMails()->count() + $contact->receivedMails()->count();

        if ($mailsCount > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer ce contact car il est associé à des courriers.');
        }

        $contact->delete();

        return redirect()->route('external.contacts.index')
            ->with('success', 'Contact externe supprimé avec succès.');
    }
}
