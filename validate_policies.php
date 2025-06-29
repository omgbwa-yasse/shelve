#!/usr/bin/env php
<?php

/**
 * Script de validation des migrations de Policies
 */

function validatePolicyFile($filePath) {
    $content = file_get_contents($filePath);
    $filename = basename($filePath);
    $issues = [];

    // Vérifier la présence de ?User $user dans les méthodes principales
    $methods = ['viewAny', 'view', 'create', 'update', 'delete', 'forceDelete', 'restore'];

    foreach ($methods as $method) {
        if (preg_match("/public function {$method}\(User \\\$user/", $content)) {
            $issues[] = "❌ Méthode {$method} n'utilise pas ?User \$user";
        } elseif (preg_match("/public function {$method}\(\?User \\\$user/", $content)) {
            $issues[] = "✅ Méthode {$method} utilise ?User \$user";
        }
    }

    // Vérifier la présence de checkOrganisationAccess dupliquée
    if (preg_match("/private function checkOrganisationAccess/", $content)) {
        $issues[] = "⚠️  Méthode checkOrganisationAccess dupliquée détectée";
    }

    // Vérifier les commentaires de documentation
    $hasGuestComments = preg_match("/Supports guest users with optional type-hint/", $content);
    if ($hasGuestComments) {
        $issues[] = "✅ Documentation Guest Users présente";
    } else {
        $issues[] = "❌ Documentation Guest Users manquante";
    }

    return [
        'file' => $filename,
        'issues' => $issues,
        'migrated' => count(array_filter($issues, fn($issue) => str_starts_with($issue, '✅'))) > 0
    ];
}

// Analyse de toutes les policies
$policiesDir = __DIR__ . '/app/Policies';
$results = [];
$excludeFiles = ['BasePolicy.php', 'PublicBasePolicy.php'];

$files = array_diff(
    array_filter(scandir($policiesDir), fn($file) => str_ends_with($file, '.php')),
    $excludeFiles
);

echo "🔍 Validation des migrations de Policies\n";
echo "=" . str_repeat("=", 50) . "\n\n";

foreach ($files as $file) {
    $filePath = $policiesDir . '/' . $file;
    if (file_exists($filePath)) {
        $results[] = validatePolicyFile($filePath);
    }
}

// Affichage des résultats
$migratedCount = 0;
$totalCount = count($results);

foreach ($results as $result) {
    echo "📄 {$result['file']}\n";
    foreach ($result['issues'] as $issue) {
        echo "   {$issue}\n";
    }
    echo "\n";

    if ($result['migrated']) {
        $migratedCount++;
    }
}

// Résumé
echo "📊 RÉSUMÉ\n";
echo "=" . str_repeat("=", 30) . "\n";
echo "✅ Policies migrées: {$migratedCount}/{$totalCount}\n";
echo "📈 Progression: " . round(($migratedCount / $totalCount) * 100, 1) . "%\n";

if ($migratedCount === $totalCount) {
    echo "\n🎉 TOUTES LES POLICIES ONT ÉTÉ MIGRÉES !\n";
    echo "🚀 Prochaines étapes :\n";
    echo "   1. Tester les autorisations Guest\n";
    echo "   2. Vérifier les performances\n";
    echo "   3. Mettre à jour la documentation\n";
} else {
    echo "\n⚠️  Migration incomplète\n";
    echo "📋 Policies restantes à migrer: " . ($totalCount - $migratedCount) . "\n";
}

echo "\n💡 Tips :\n";
echo "   - Utilisez @can/@endcan dans vos templates\n";
echo "   - Testez les scénarios Guest Users\n";
echo "   - Documentez les permissions spécifiques\n";
