<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\models\Role;


class RoleController extends Controller
{

    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }



    public function create()
    {
        return view('roles.create');
    }



    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles|max:255',
            'description' => 'nullable|string',
        ]);

        Role::create($request->all());

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }



    public function show(Role $role)
    {
        return view('roles.show', compact('role'));
    }



    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }



    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,'.$role->id.'|max:255',
            'description' => 'nullable|string',
        ]);

        $role->update($request->all());

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }


    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }


}
