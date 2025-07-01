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
        // Récupérer tous les rôles
        $roles = Role::all();

        // Grouper les permissions par catégorie
        $permissions = Permission::all();
        $permissionsByCategory = $permissions->groupBy('category');

        // Définir l'ordre des catégories et leurs labels
        $categoryLabels = [
            'dashboard' => 'Tableau de bord',
            'mail' => 'Courrier',
            'records' => 'Documents',
            'communications' => 'Communications',
            'reservations' => 'Réservations',
            'users' => 'Utilisateurs et Rôles',
            'settings' => 'Paramètres',
            'system' => 'Système',
            'backups' => 'Sauvegardes'
        ];

        // Créer une matrice des permissions par rôle
        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role->id] = $role->permissions()->pluck('permissions.id')->toArray();
        }

        return view('role_permissions.index', compact('roles', 'permissionsByCategory', 'categoryLabels', 'rolePermissions'));
    }




    public function create()
    {
        $roles = Role::all();

        // Grouper les permissions par catégorie
        $permissions = Permission::all();
        $permissionsByCategory = $permissions->groupBy('category');

        // Définir l'ordre des catégories et leurs labels
        $categoryLabels = [
            'dashboard' => 'Tableau de bord',
            'mail' => 'Courrier',
            'records' => 'Documents',
            'communications' => 'Communications',
            'reservations' => 'Réservations',
            'users' => 'Utilisateurs et Rôles',
            'settings' => 'Paramètres',
            'system' => 'Système',
            'backups' => 'Sauvegardes',
            'transfers' => 'Transferts',
            'deposits' => 'Dépôts',
            'reports' => 'Rapports',
            'tools' => 'Outils',
            'ai' => 'Intelligence Artificielle',
            'search' => 'Recherche',
            'thesaurus' => 'Thésaurus',
            'organizations' => 'Organisations'
        ];

        return view('role_permissions.create', compact('roles', 'permissionsByCategory', 'categoryLabels'));
    }





    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
        ]);

        $role = Role::findOrFail($validatedData['role_id']);

        $role->permissions()->detach();

        foreach ($validatedData['permissions'] as $permissionId) {
            $permission = Permission::findOrFail($permissionId);
            $role->permissions()->attach($permission);
        }

        return redirect()->route('role_permissions.index')->with('success', 'Permissions saved successfully.');
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


    public function getRolePermissions(Request $request, $roleId)
    {
        try {
            $role = Role::findOrFail($roleId);
            $permissions = $role->permissions()->pluck('permissions.id')->toArray();

            return response()->json([
                'success' => true,
                'permissions' => $permissions,
                'role_name' => $role->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des permissions'
            ], 500);
        }
    }

    public function updateMatrix(Request $request)
    {
        try {
            // Récupérer toutes les permissions soumises
            $permissions = $request->input('permissions', []);

            // Récupérer tous les rôles
            $roles = Role::all();

            foreach ($roles as $role) {
                $rolePermissions = $permissions[$role->id] ?? [];

                // Détacher toutes les permissions actuelles du rôle
                $role->permissions()->detach();

                // Attacher les nouvelles permissions
                if (!empty($rolePermissions)) {
                    $role->permissions()->attach($rolePermissions);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Permissions mises à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des permissions: ' . $e->getMessage()
            ], 500);
        }
    }

}
