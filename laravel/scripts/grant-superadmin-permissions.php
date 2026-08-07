<?php
/**
 * Script to grant all permissions to superadmin role and user
 * Usage: php artisan tinker scripts/grant-superadmin-permissions.php
 */

// Step 1: Find the superadmin role
$superadminRole = DB::table('roles')->where('name', 'superadmin')->first();
if (!$superadminRole) {
    echo "ERROR: SuperAdmin role not found!\n";
    exit(1);
}
echo "SuperAdmin Role: ID={$superadminRole->id}, Name={$superadminRole->name}\n";

// Step 2: Get all permissions
$allPermissions = DB::table('permissions')->get();
echo "Total permissions in database: {$allPermissions->count()}\n\n";

foreach ($allPermissions as $perm) {
    echo "  [{$perm->id}] {$perm->name}" . ($perm->category ? " ({$perm->category})" : "") . "\n";
}

// Step 3: Check current state
$currentRolePerms = DB::table('role_has_permissions')->where('role_id', $superadminRole->id)->pluck('permission_id')->toArray();
$currentRolePerms2 = DB::table('role_permissions')->where('role_id', $superadminRole->id)->pluck('permission_id')->toArray();
echo "\nCurrent: role_has_permissions = " . count($currentRolePerms) . ", role_permissions = " . count($currentRolePerms2) . "\n";

// Step 4: Grant all permissions via role_has_permissions (Spatie-style pivot)
$inserted1 = 0;
foreach ($allPermissions as $perm) {
    $exists = DB::table('role_has_permissions')
        ->where('role_id', $superadminRole->id)
        ->where('permission_id', $perm->id)
        ->exists();
    if (!$exists) {
        DB::table('role_has_permissions')->insert([
            'role_id' => $superadminRole->id,
            'permission_id' => $perm->id,
        ]);
        $inserted1++;
        echo "  [role_has_permissions] Granted: {$perm->name}\n";
    } else {
        echo "  [role_has_permissions] Already has: {$perm->name}\n";
    }
}

// Step 5: Grant all permissions via role_permissions (custom pivot)
$inserted2 = 0;
foreach ($allPermissions as $perm) {
    $exists = DB::table('role_permissions')
        ->where('role_id', $superadminRole->id)
        ->where('permission_id', $perm->id)
        ->exists();
    if (!$exists) {
        DB::table('role_permissions')->insert([
            'role_id' => $superadminRole->id,
            'permission_id' => $perm->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $inserted2++;
        echo "  [role_permissions] Granted: {$perm->name}\n";
    } else {
        echo "  [role_permissions] Already has: {$perm->name}\n";
    }
}

// Step 6: Find superadmin user and ensure role assignment
$superadminUser = \App\Models\User::where('email', 'superadmin@example.com')->first();
if ($superadminUser) {
    echo "\nSuperAdmin User: {$superadminUser->name} (ID={$superadminUser->id})\n";

    // Ensure model_has_roles link exists
    $hasModelRole = DB::table('model_has_roles')
        ->where('role_id', $superadminRole->id)
        ->where('model_type', get_class($superadminUser))
        ->where('model_id', $superadminUser->id)
        ->exists();
    if (!$hasModelRole) {
        DB::table('model_has_roles')->insert([
            'role_id' => $superadminRole->id,
            'model_type' => get_class($superadminUser),
            'model_id' => $superadminUser->id,
        ]);
        echo "  [model_has_roles] Linked superadmin role to user\n";
    } else {
        echo "  [model_has_roles] Already linked\n";
    }

    // Ensure user_roles link exists
    $hasUserRole = DB::table('user_roles')
        ->where('role_id', $superadminRole->id)
        ->where('user_id', $superadminUser->id)
        ->exists();
    if (!$hasUserRole) {
        DB::table('user_roles')->insert([
            'user_id' => $superadminUser->id,
            'role_id' => $superadminRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "  [user_roles] Linked superadmin role to user\n";
    } else {
        echo "  [user_roles] Already linked\n";
    }

    // Also grant all permissions directly to user via model_has_permissions
    $directInserted = 0;
    foreach ($allPermissions as $perm) {
        $exists = DB::table('model_has_permissions')
            ->where('permission_id', $perm->id)
            ->where('model_type', get_class($superadminUser))
            ->where('model_id', $superadminUser->id)
            ->exists();
        if (!$exists) {
            DB::table('model_has_permissions')->insert([
                'permission_id' => $perm->id,
                'model_type' => get_class($superadminUser),
                'model_id' => $superadminUser->id,
            ]);
            $directInserted++;
        }
    }
    echo "  [model_has_permissions] Direct user permissions granted: $directInserted\n";

    // Also via user_permissions
    $directInserted2 = 0;
    foreach ($allPermissions as $perm) {
        $exists = DB::table('user_permissions')
            ->where('permission_id', $perm->id)
            ->where('user_id', $superadminUser->id)
            ->exists();
        if (!$exists) {
            DB::table('user_permissions')->insert([
                'user_id' => $superadminUser->id,
                'permission_id' => $perm->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $directInserted2++;
        }
    }
    echo "  [user_permissions] Direct user permissions granted: $directInserted2\n";
} else {
    echo "\nWARNING: No superadmin user found at superadmin@example.com\n";
}

// Step 7: Final summary
echo "\n=== SUMMARY ===\n";
echo "role_has_permissions: " . DB::table('role_has_permissions')->where('role_id', $superadminRole->id)->count() . " / {$allPermissions->count()}\n";
echo "role_permissions: " . DB::table('role_permissions')->where('role_id', $superadminRole->id)->count() . " / {$allPermissions->count()}\n";
if ($superadminUser) {
    echo "model_has_roles: " . DB::table('model_has_roles')->where('model_id', $superadminUser->id)->where('model_type', get_class($superadminUser))->count() . " role(s)\n";
    echo "user_roles: " . DB::table('user_roles')->where('user_id', $superadminUser->id)->count() . " role(s)\n";
    echo "model_has_permissions: " . DB::table('model_has_permissions')->where('model_id', $superadminUser->id)->where('model_type', get_class($superadminUser))->count() . " / {$allPermissions->count()}\n";
    echo "user_permissions: " . DB::table('user_permissions')->where('user_id', $superadminUser->id)->count() . " / {$allPermissions->count()}\n";
}
echo "\nAll permissions granted to SuperAdmin! ✓\n";
