<?php


namespace App\Http\Controllers;
use App\Models\UserOrganisationRole;
use Illuminate\Http\Request;



class UserOrganisationRoleController extends Controller{


    public function index()
    {
        $UserOrganisationRoles = UserOrganisationRole::with('user', 'organisation','role')->get();
        return view('user_organisation_role.index', compact('UserOrganisationRoles'));
    }



    public function create()
    {
        return view('user_organisation_role.create');
    }



    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
            'role_id' => 'required|exists:roles,id',
            'active' => 'required|boolean',
        ]);
        UserOrganisationRole::create($request->all());
        return redirect()->route('user-organisation-role.index')
            ->with('success', 'User Organisation created successfully.');
    }




    public function show(INT $organisation_id)
    {
        UserOrganisationRole::where('organisation_id', $organisation_id)->get();
        return view('user_organisation_role.show', compact('UserOrganisationRole'));
    }




    public function edit(INT $organisation_id)
    {
        UserOrganisationRole::where('organisation_id', $organisation_id)->get();
        return view('user_organisation_role.edit', compact('UserOrganisationRole'));
    }





    public function update(Request $request, INT $organisation_id)
    {
        $UserOrganisationRole = UserOrganisationRole::where('organisation_id', $organisation_id)->get();
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
            'role_id' => 'required|exists:roles,id',
            'active' => 'required|boolean',
        ]);

        $UserOrganisationRole->update($request->all());

        return redirect()->route('user-organisation-role.index')
            ->with('success', 'User Organisation updated successfully');
    }




    public function destroy(INT $organisation_id)
    {
        $UserOrganisationRole = UserOrganisationRole::where('organisation_id', $organisation_id)->get();
        $UserOrganisationRole->delete();
        return redirect()->route('user-organisation-role.index')
            ->with('success', 'User Organisation deleted successfully');
    }
}
