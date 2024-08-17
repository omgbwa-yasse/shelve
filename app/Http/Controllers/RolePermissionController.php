<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{

    public function index()
    {
        $rolePermissions = RolePermission::with('role', 'permission')->get();
        return view('role_permissions.index', compact('rolePermissions'));
    }




    public function create()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('role_permissions.create', compact('roles', 'permissions'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        RolePermission::create($request->all());

        return redirect()->route('role_permissions.index')
            ->with('success', 'Role permission created successfully.');
    }




    public function show(Role $role, Permission $permission)
    {
        $rolePermission = RolePermission::where('role_id', $role->id)
            ->where('permission_id', $permission->id)
            ->get();

        return view('role_permissions.show', compact('rolePermission'));
    }



    public function edit(Role $role, Permission $permission)
    {
        $rolePermission = RolePermission::where('role_id', $role->id)
            ->where('permission_id', $permission->id)
            ->get();

        $roles = Role::all();
        $permissions = permission::all();

        dd($role);
        return view('role_permissions.edit', compact('rolePermission', 'roles', 'permissions'));
    }




    public function update(Request $request, Role $role, Permission $permission)
    {
        $rolePermission = RolePermission::where('role_id', $role->id)
            ->where('permission_id', $permission->id)
            ->get();

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $rolePermission->update($request->all());

        return redirect()->route('role_permissions.index')
            ->with('success', 'Role permission updated successfully.');
    }


    public function destroy(Role $role, Permission $permission)
    {
        $rolePermission = RolePermission::where('role_id', $role->id)
            ->where('permission_id', $permission->id)
            ->get();
        $rolePermission->delete();
        return redirect()->route('role_permissions.index')
            ->with('success', 'Role permission deleted successfully.');
    }


}
