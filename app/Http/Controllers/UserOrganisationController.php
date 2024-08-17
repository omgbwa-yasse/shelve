<?php

namespace App\Http\Controllers;

use App\Models\UserOrganisation;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Http\Request;

class UserOrganisationController extends Controller
{
    public function index()
    {
        $userOrganisations = UserOrganisation::all();
        return view('user_organisations.index', compact('userOrganisations'));
    }



    public function create()
    {
        $users = User::all();
        $organisations = Organisation::all();
        return view('user_organisations.create', compact('users','organisations'));
    }




    public function store(Request $request)
    {

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
        ]);

        UserOrganisation::create($request->all());

        return redirect()->route('user-organisations.index')
            ->with('success', 'User Organisation created successfully.');
    }




    public function show(INT $id)
    {
        $userOrganisation = UserOrganisation::where($id);
        return view('user_organisations.show', compact('userOrganisation'));
    }




    public function edit(INT $id)
    {
        $userOrganisation = UserOrganisation::where($id);
        return view('user_organisations.edit', compact('userOrganisation'));
    }




    public function update(Request $request, INT $id)
    {

        $userOrganisation = UserOrganisation::where($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
        ]);

        $userOrganisation->update($request->all());

        return redirect()->route('user-organisations.index')
            ->with('success', 'User Organisation updated successfully.');
    }



    public function destroy(INT $id)
    {
        $userOrganisation = UserOrganisation::where($id);
        $userOrganisation->delete();

        return redirect()->route('user-organisations.index')
            ->with('success', 'User Organisation deleted successfully.');
    }


}
