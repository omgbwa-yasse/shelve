<?php

/**
 * Script de synchronisation des permissions SuperAdmin
 *
 * Usage: php scripts/sync-superadmin-permissions.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\Role;

echo "=== Synchronisation des Permissions SuperAdmin ===\n\n";

// 1. Récupérer le rôle superadmin
$superadminRole = Role::where('name', 'superadmin')->first();

if (!$superadminRole) {
    echo "❌ Erreur: Rôle 'superadmin' non trouvé\n";
    exit(1);
}

echo "📋 Rôle SuperAdmin trouvé (ID: {$superadminRole->id})\n";
echo "   Permissions actuelles: " . $superadminRole->permissions()->count() . "\n\n";

// 2. Récupérer TOUTES les permissions
$allPermissions = Permission::all();
echo "📊 Permissions totales dans la base: " . $allPermissions->count() . "\n\n";

// 3. Afficher les nouvelles permissions WorkPlace
$workplacePermissions = Permission::where('category', 'workplace')->get();
echo "🆕 Permissions WorkPlace: " . $workplacePermissions->count() . "\n";

$moduleWorkplaceAccess = Permission::where('name', 'module_workplace_access')->first();
if ($moduleWorkplaceAccess) {
    echo "🔐 Module access: module_workplace_access (ID: {$moduleWorkplaceAccess->id})\n\n";
}

// 4. Synchroniser toutes les permissions avec le rôle superadmin
echo "🔄 Synchronisation de toutes les permissions avec le rôle superadmin...\n";

$permissionIds = $allPermissions->pluck('id')->toArray();
$superadminRole->permissions()->sync($permissionIds);

// 5. Vérifier la synchronisation
$newCount = $superadminRole->permissions()->count();
echo "✅ Synchronisation terminée!\n\n";

echo "📊 Résumé:\n";
echo "   - Permissions totales: " . $allPermissions->count() . "\n";
echo "   - Permissions du rôle superadmin: " . $newCount . "\n";

if ($newCount === $allPermissions->count()) {
    echo "\n✅ SUCCÈS: Toutes les permissions sont attribuées au SuperAdmin!\n";
} else {
    echo "\n❌ ERREUR: Certaines permissions ne sont pas attribuées\n";
    echo "   Écart: " . ($allPermissions->count() - $newCount) . " permissions manquantes\n";
}

// 6. Afficher les catégories de permissions
echo "\n📋 Répartition par catégorie:\n";
$categories = $allPermissions->groupBy('category');
foreach ($categories as $category => $perms) {
    $categoryName = $category ?: 'Non catégorisée';
    echo "   • " . ucfirst($categoryName) . ": " . $perms->count() . " permissions\n";
}

echo "\n=== Fin de la synchronisation ===\n";
