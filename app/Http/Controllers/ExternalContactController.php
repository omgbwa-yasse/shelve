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

        // These relations will return empty collections until migration is applied
        $sentMails = collect(); // Empty collection for now
        $receivedMails = collect(); // Empty collection for now

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

    /**
     * API method to get contacts list
     */
    public function apiIndex(Request $request)
    {
        $query = ExternalContact::with('organization');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('organization', function($orgQuery) use ($search) {
                      $orgQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('organization_id')) {
            $query->where('external_organization_id', $request->input('organization_id'));
        }

        $contacts = $query->orderBy('last_name')
                         ->orderBy('first_name')
                         ->get()
                         ->map(function($contact) {
                             return [
                                 'id' => $contact->id,
                                 'full_name' => $contact->full_name,
                                 'first_name' => $contact->first_name,
                                 'last_name' => $contact->last_name,
                                 'email' => $contact->email,
                                 'phone' => $contact->phone,
                                 'position' => $contact->position,
                                 'organization' => $contact->organization ? [
                                     'id' => $contact->organization->id,
                                     'name' => $contact->organization->name,
                                     'city' => $contact->organization->city,
                                 ] : null,
                             ];
                         });

        return response()->json(['contacts' => $contacts]);
    }

    /**
     * API method to search contacts
     */
    public function apiSearch(Request $request)
    {
        $search = $request->input('q', '');

        $contacts = ExternalContact::with('organization')
            ->where(function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->orWhereHas('organization', function($orgQuery) use ($search) {
                $orgQuery->where('name', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(function($contact) {
                return [
                    'id' => $contact->id,
                    'text' => $contact->full_name .
                             ($contact->organization ? ' (' . $contact->organization->name . ')' : '') .
                             ($contact->email ? ' - ' . $contact->email : ''),
                    'full_name' => $contact->full_name,
                    'email' => $contact->email,
                    'organization' => $contact->organization ? $contact->organization->name : null,
                ];
            });

        return response()->json(['results' => $contacts]);
    }

    /**
     * API method to show a specific contact
     */
    public function apiShow($id)
    {
        $contact = ExternalContact::with('organization')->findOrFail($id);

        return response()->json([
            'contact' => [
                'id' => $contact->id,
                'full_name' => $contact->full_name,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'position' => $contact->position,
                'address' => $contact->address,
                'is_primary_contact' => $contact->is_primary_contact,
                'is_verified' => $contact->is_verified,
                'notes' => $contact->notes,
                'organization' => $contact->organization ? [
                    'id' => $contact->organization->id,
                    'name' => $contact->organization->name,
                    'city' => $contact->organization->city,
                    'address' => $contact->organization->address,
                ] : null,
            ]
        ]);
    }
}
