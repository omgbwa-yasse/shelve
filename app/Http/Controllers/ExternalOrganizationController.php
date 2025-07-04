<?php

namespace App\Http\Controllers;

use App\Models\ExternalOrganization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExternalOrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organizations = ExternalOrganization::orderBy('name')->paginate(10);
        return view('external.organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('external.organizations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'legal_form' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $organization = ExternalOrganization::create($request->all());

        return redirect()->route('external.organizations.show', $organization)
            ->with('success', 'Organisation externe créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $organization = ExternalOrganization::with('contacts')->findOrFail($id);
        $sentMails = $organization->sentMails()->with('recipient')->take(5)->get();
        $receivedMails = $organization->receivedMails()->with('sender')->take(5)->get();

        return view('external.organizations.show', compact('organization', 'sentMails', 'receivedMails'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $organization = ExternalOrganization::findOrFail($id);
        return view('external.organizations.edit', compact('organization'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $organization = ExternalOrganization::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'legal_form' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $organization->update($request->all());

        return redirect()->route('external.organizations.show', $organization)
            ->with('success', 'Organisation externe mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $organization = ExternalOrganization::findOrFail($id);

        // Vérifier si l'organisation est utilisée dans des courriers
        $mailsCount = $organization->sentMails()->count() + $organization->receivedMails()->count();

        if ($mailsCount > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette organisation car elle est associée à des courriers.');
        }

        // Dissocier les contacts de cette organisation
        $organization->contacts()->update(['external_organization_id' => null]);

        $organization->delete();

        return redirect()->route('external.organizations.index')
            ->with('success', 'Organisation externe supprimée avec succès.');
    }
}
