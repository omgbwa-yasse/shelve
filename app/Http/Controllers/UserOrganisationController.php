<?php


namespace App\Http\Controllers;
use App\Models\UserOrganisation;
use Illuminate\Http\Request;



class UserOrganisationController extends Controller{


    public function index()
    {
        $userOrganisations = UserOrganisation::with('user', 'organisation')->get();
        return view('organisations.user.index', compact('userOrganisations'));
    }



    public function create()
    {
        return view('organisations.user.create');
    }



    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
            'active' => 'required|boolean',
        ]);

        UserOrganisation::create($request->all());

        return redirect()->route('user-organisation.index')
            ->with('success', 'User Organisation created successfully.');
    }




    public function show(UserOrganisation $userOrganisation)
    {
        return view('organisations.user.show', compact('userOrganisation'));
    }




    public function edit(UserOrganisation $userOrganisation)
    {
        return view('organisations.user.edit', compact('userOrganisation'));
    }





    public function update(Request $request, UserOrganisation $userOrganisation)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
            'active' => 'required|boolean',
        ]);

        $userOrganisation->update($request->all());

        return redirect()->route('user-organisation.index')
            ->with('success', 'User Organisation updated successfully');
    }




    public function destroy(UserOrganisation $userOrganisation)
    {
        $userOrganisation->delete();

        return redirect()->route('user-organisation.index')
            ->with('success', 'User Organisation deleted successfully');
    }
}
