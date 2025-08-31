<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Organisation;
use Illuminate\Http\Request;

class OrganisationContactController extends Controller
{
    public function index(Organisation $organisation)
    {
        $contacts = $organisation->contacts()->orderBy('type')->orderBy('value')->get();
        return view('organisations.contacts.index', compact('organisation', 'contacts'));
    }

    public function create(Organisation $organisation)
    {
        return view('organisations.contacts.create', compact('organisation'));
    }

    public function store(Request $request, Organisation $organisation)
    {
        $validated = $this->validateContact($request);
        $contact = Contact::create($validated);
        $organisation->contacts()->attach($contact->id);
        return redirect()->route('organisations.contacts.index', $organisation)->with('success', __('Contact created.'));
    }

    public function edit(Organisation $organisation, Contact $contact)
    {
        return view('organisations.contacts.edit', compact('organisation', 'contact'));
    }

    public function show(Organisation $organisation, Contact $contact)
    {
        // Optional guard: ensure the contact belongs to this organisation
        if (! $organisation->contacts()->where('contacts.id', $contact->id)->exists()) {
            abort(404);
        }
        $contacts = $organisation->contacts()->orderBy('type')->orderBy('value')->get();
        return view('organisations.contacts.show', compact('organisation', 'contact', 'contacts'));
    }

    public function update(Request $request, Organisation $organisation, Contact $contact)
    {
        $validated = $this->validateContact($request);
        $contact->update($validated);
        // Ensure relation exists
        if (! $organisation->contacts()->where('contacts.id', $contact->id)->exists()) {
            $organisation->contacts()->attach($contact->id);
        }
        return redirect()->route('organisations.contacts.index', $organisation)->with('success', __('Contact updated.'));
    }

    public function destroy(Organisation $organisation, Contact $contact)
    {
        // Detach relation and optionally delete contact if orphan
        $organisation->contacts()->detach($contact->id);
        if (! $contact->organisations()->exists()) {
            $contact->delete();
        }
        return redirect()->route('organisations.contacts.index', $organisation)->with('success', __('Contact deleted.'));
    }

    private function validateContact(Request $request): array
    {
        $types = ['email','telephone','gps','fax','code_postal','adresse'];
        return $request->validate([
            'type' => 'required|in:' . implode(',', $types),
            'value' => 'required|string|max:5000',
            'label' => 'nullable|string|max:190',
            'notes' => 'nullable|string',
        ]);
    }
}
