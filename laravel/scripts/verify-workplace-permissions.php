<?php

/**
 * Script de vérification des permissions WorkPlace
 *
 * Usage: php scripts/verify-workplace-permissions.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\User;
use App\Models\Role;

echo "=== Vérification des Permissions WorkPlace ===\n\n";

// 1. Vérifier les permissions WorkPlace
echo "📊 Permissions WorkPlace (catégorie 'workplace'):\n";
$workplacePermissions = Permission::where('category', 'workplace')->orderBy('name')->get();
echo "   Total: " . $workplacePermissions->count() . " permissions\n\n";

$grouped = $workplacePermissions->groupBy(function($permission) {
    $parts = explode('_', $permission->name);
    if (count($parts) >= 2) {
        return $parts[0] . '_' . $parts[1]; // workplace_member, workplace_template, etc.
    }
    return $parts[0];
});

foreach ($grouped as $prefix => $perms) {
    echo "   • " . str_replace('workplace_', '', $prefix) . ": " . $perms->count() . " permissions\n";
    foreach ($perms as $perm) {
        echo "      - " . $perm->name . "\n";
    }
    echo "\n";
}

// 2. Vérifier la permission module_workplace_access
echo "🔐 Permission d'accès au module:\n";
$moduleAccess = Permission::where('name', 'module_workplace_access')->first();
if ($moduleAccess) {
    echo "   ✅ module_workplace_access trouvée (ID: {$moduleAccess->id}, Catégorie: {$moduleAccess->category})\n";
} else {
    echo "   ❌ module_workplace_access NON TROUVÉE\n";
}
echo "\n";

// 3. Vérifier le SuperAdmin
echo "👤 Vérification du SuperAdmin:\n";
$superadmin = User::where('email', 'superadmin@example.com')->first();

if ($superadmin) {
    echo "   Utilisateur: {$superadmin->name} {$superadmin->surname} ({$superadmin->email})\n";

    // Vérifier le rôle
    $role = $superadmin->roles()->first();
    if ($role) {
        echo "   Rôle: {$role->name}\n";
        $rolePermCount = $role->permissions()->count();
        echo "   Permissions du rôle: {$rolePermCount}\n";
    }

    // Vérifier permissions WorkPlace
    $hasModuleAccess = $superadmin->hasPermissionTo('module_workplace_access');
    echo "   \n";
    echo "   Module Access: " . ($hasModuleAccess ? "✅ OUI" : "❌ NON") . "\n";

    $testPermissions = [
        'workplace_create',
        'workplace_viewAny',
        'workplace_member_add',
        'workplace_invitation_create',
        'workplace_folder_share',
        'workplace_template_viewAny'
    ];

    echo "   \n";
    echo "   Permissions de test:\n";
    foreach ($testPermissions as $testPerm) {
        $has = $superadmin->hasPermissionTo($testPerm);
        echo "      " . ($has ? "✅" : "❌") . " {$testPerm}\n";
    }

} else {
    echo "   ❌ SuperAdmin non trouvé\n";
}

echo "\n=== Fin de la vérification ===\n";
