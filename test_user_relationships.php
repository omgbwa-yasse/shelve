<?php

/**
 * Simple test script to verify User model relationships work correctly
 */

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Organisation;

echo "🔍 Testing User model relationships...\n\n";

try {
    // Get first user
    $user = User::first();
    
    if (!$user) {
        echo "❌ No users found in database\n";
        exit(1);
    }
    
    echo "✅ User found: {$user->name} (ID: {$user->id})\n";
    echo "   Current Organisation ID: " . ($user->current_organisation_id ?? 'null') . "\n";
    
    // Test organisation relationship
    try {
        $org = $user->organisation;
        if ($org) {
            echo "✅ organisation() relationship works: {$org->name}\n";
        } else {
            echo "ℹ️ organisation() relationship returns null (user has no current organisation)\n";
        }
    } catch (Exception $e) {
        echo "❌ organisation() relationship failed: " . $e->getMessage() . "\n";
    }
    
    // Test currentOrganisation relationship (our new alias)
    try {
        $currentOrg = $user->currentOrganisation;
        if ($currentOrg) {
            echo "✅ currentOrganisation() relationship works: {$currentOrg->name}\n";
        } else {
            echo "ℹ️ currentOrganisation() relationship returns null (user has no current organisation)\n";
        }
    } catch (Exception $e) {
        echo "❌ currentOrganisation() relationship failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 All relationship tests completed!\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
