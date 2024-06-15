<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOrganisation;
use App\Models\Organisation;
use Illuminate\Http\Request;

class UserOrganisationController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $userOrganisations = UserOrganisation::where('user_id', $user->id)->get();
        return view('userOrganisations.index', compact('userOrganisations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
            'active' => 'boolean',
        ]);

        $userOrganisation = UserOrganisation::create([
            'user_id' => $request->user_id,
            'organisation_id' => $request->organisation_id,
            'active' => $request->active ?? false,
        ]);

        return redirect()->route('userOrganisations.index')->with('success', 'User organisation created successfully.');
    }




    public function update(Request $request, $user_id, $organisation_id)
    {
        $request->validate([
            'active' => 'boolean',
        ]);

        $userOrganisation = UserOrganisation::where('user_id', $user_id)
            ->where('organisation_id', $organisation_id)
            ->firstOrFail();

        $userOrganisation->update([
            'active' => $request->active ?? false,
        ]);

        return redirect()->route('userOrganisations.index')->with('success', 'User organisation updated successfully.');
    }




    public function destroy($user_id, $organisation_id)
    {
        $userOrganisation = UserOrganisation::where('user_id', $user_id)
            ->where('organisation_id', $organisation_id)
            ->firstOrFail();

        $userOrganisation->delete();

        return redirect()->route('userOrganisations.index')->with('success', 'User organisation deleted successfully.');
    }



}
