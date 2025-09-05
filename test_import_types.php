<?php

require_once 'vendor/autoload.php';

use App\Imports\RecordsImport;
use App\Models\Dolly;

// Test des conversions de types
echo "=== Test des conversions de types ===\n";

// Créer un Dolly de test
$dolly = Dolly::create([
    'name' => 'Test Types ' . now()->format('Y-m-d H:i:s'),
    'description' => 'Test des conversions de types',
    'category' => 'record',
    'is_public' => false,
    'created_by' => 1,
    'owner_organisation_id' => 1,
]);

// Créer une instance d'import
$import = new RecordsImport($dolly, [], false, false);

// Test avec différents types de données
$testData = [
    // Données normales
    ['F001', 'Test normal', 'fonds', 'actif', 'papier', 'administration', 'Contenu normal'],
    
    // Données avec tableaux
    [['F002'], ['Test', 'tableau'], 'fonds', 'actif', 'papier', 'administration', 'Contenu avec tableau'],
    
    // Données avec objets (simulés)
    [new class { public function __toString() { return 'F003'; } }, 'Test objet', 'fonds', 'actif', 'papier', 'administration', 'Contenu avec objet'],
    
    // Données avec nombres
    [123, 'Test nombre', 'fonds', 'actif', 'papier', 'administration', 'Contenu avec nombre'],
    
    // Données mixtes
    [['F004', 'F005'], 'Test mixte', 'fonds', 'actif', 'papier', 'administration', 'Contenu mixte'],
];

echo "Test des conversions de types...\n";

foreach ($testData as $index => $row) {
    echo "Ligne " . ($index + 1) . ": ";
    
    try {
        $result = $import->model($row);
        if ($result) {
            echo "✓ Importée - Code: {$result->code}, Nom: {$result->name}\n";
        } else {
            echo "✗ Ignorée\n";
        }
    } catch (Exception $e) {
        echo "✗ Erreur: " . $e->getMessage() . "\n";
    }
}

// Afficher le résumé
$summary = $import->getImportSummary();
echo "\n=== Résumé ===\n";
echo "Importés: {$summary['imported']}\n";
echo "Ignorés: {$summary['skipped']}\n";
echo "Erreurs: {$summary['errors']}\n";

if ($summary['errors'] > 0) {
    echo "\n=== Détails des erreurs ===\n";
    foreach ($summary['errors'] as $error) {
        echo "Ligne {$error['row']}: {$error['error']}\n";
    }
}

echo "\nTest terminé.\n";
