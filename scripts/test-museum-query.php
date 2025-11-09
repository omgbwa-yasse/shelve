<?php

use App\Models\RecordArtifact;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST DE LA REQUÊTE COLLECTIONS ===\n\n";

try {
    // Test 1: Compter tous les artifacts
    $totalArtifacts = RecordArtifact::count();
    echo "✅ Total artifacts: $totalArtifacts\n\n";

    // Test 2: Requête des collections (par catégorie)
    $collections = RecordArtifact::selectRaw('category, COUNT(*) as pieces_count')
        ->whereNotNull('category')
        ->groupBy('category')
        ->get();

    echo "✅ Nombre de catégories (collections): " . $collections->count() . "\n\n";

    if ($collections->count() > 0) {
        echo "📊 Répartition par catégorie:\n";
        foreach ($collections as $collection) {
            echo "   • " . ($collection->category ?? 'Non définie') . ": " . $collection->pieces_count . " pièce(s)\n";
        }
    } else {
        echo "ℹ️  Aucune catégorie trouvée (base de données vide ou pas d'artifacts avec catégorie)\n";
    }

    echo "\n✅ REQUÊTE FONCTIONNELLE - Pas d'erreur SQL!\n";

} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . "\n";
    echo "Ligne: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n=== TEST TERMINÉ AVEC SUCCÈS ===\n";
