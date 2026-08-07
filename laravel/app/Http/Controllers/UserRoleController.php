<?php


namespace App\Http\Controllers;

use App\Models\UserRole;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function index()
    {
        $userRoles = UserRole::all();

        return view('user_roles.index', compact('userRoles'));
    }

    public function create()
    {
        return view('user_roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'user_id' => 'required|exists:users,id',
        ]);

        UserRole::create($request->all());

        return redirect()->route('user_roles.index')
            ->with('success', 'User Role created successfully.');
    }





    public function show(INT $id)
    {
        $userRole = UserRole::where($id);
        return view('user_roles.show', compact('userRole'));
    }




    public function edit(INT $id)
    {
        $userRole = UserRole::where($id);
        return view('user_roles.edit', compact('userRole'));
    }



    public function update(Request $request, INT $id)
    {
        $userRole = UserRole::where($id);
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $userRole->update($request->all());

        return redirect()->route('user_roles.index')
            ->with('success', 'User Role updated successfully.');
    }

    public function destroy(INT $id)
    {
        $userRole = UserRole::where($id);
        $userRole->delete();

        return redirect()->route('user_roles.index')
            ->with('success', 'User Role deleted successfully.');
    }


}
