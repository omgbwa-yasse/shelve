<?php

require_once 'vendor/autoload.php';

use App\Imports\RecordsImport;
use App\Models\Dolly;
use App\Models\Record;
use Illuminate\Support\Facades\Auth;

// Test de l'import avec des données valides
echo "=== Test d'import avec données valides ===\n";

// Créer un Dolly de test
$dolly = Dolly::create([
    'name' => 'Test Import ' . now()->format('Y-m-d H:i:s'),
    'description' => 'Test d\'import automatique',
    'category' => 'record',
    'is_public' => false,
    'created_by' => 1,
    'owner_organisation_id' => 1,
]);

// Données de test
$testData = [
    ['F001', 'Fonds de la mairie', 'fonds', 'actif', 'papier', 'administration', 'Documents administratifs', '1900-01-01', '2000-12-31', 'Mairie', 'administration'],
    ['S001', 'Série des délibérations', 'série', 'actif', 'papier', 'administration', 'Procès-verbaux', '1950-01-01', '1990-12-31', 'Conseil', 'délibération'],
    ['F002', 'Fonds de l\'école', 'fonds', 'actif', 'papier', 'éducation', 'Archives scolaires', '1920-01-01', '1980-12-31', 'École', 'éducation'],
];

// Créer une instance d'import
$import = new RecordsImport($dolly, [], false, false);

// Simuler l'import ligne par ligne
foreach ($testData as $row) {
    $result = $import->model($row);
    if ($result) {
        echo "✓ Ligne importée: {$row[0]} - {$row[1]}\n";
    } else {
        echo "✗ Ligne ignorée: {$row[0]} - {$row[1]}\n";
    }
}

// Afficher le résumé
$summary = $import->getImportSummary();
echo "\n=== Résumé de l'import ===\n";
echo "Importés: {$summary['imported']}\n";
echo "Ignorés: {$summary['skipped']}\n";
echo "Erreurs: {$summary['errors']}\n";
echo "Total: {$summary['total_rows']}\n";

// Test avec des données invalides
echo "\n=== Test d'import avec données invalides ===\n";

$invalidData = [
    ['', 'Nom sans code', 'fonds', 'actif', 'papier', 'administration', 'Test', '1900-01-01', '2000-12-31', 'Test', 'test'],
    ['F003', '', 'fonds', 'actif', 'papier', 'administration', 'Test sans nom', '1900-01-01', '2000-12-31', 'Test', 'test'],
    ['F004', 'Test sans level', '', 'actif', 'papier', 'administration', 'Test', '1900-01-01', '2000-12-31', 'Test', 'test'],
];

$import2 = new RecordsImport($dolly, [], false, false);

foreach ($invalidData as $row) {
    $result = $import2->model($row);
    if ($result) {
        echo "✓ Ligne importée: {$row[0]} - {$row[1]}\n";
    } else {
        echo "✗ Ligne ignorée: {$row[0]} - {$row[1]}\n";
    }
}

$summary2 = $import2->getImportSummary();
echo "\n=== Résumé de l'import invalide ===\n";
echo "Importés: {$summary2['imported']}\n";
echo "Ignorés: {$summary2['skipped']}\n";
echo "Erreurs: {$summary2['errors']}\n";
echo "Total: {$summary2['total_rows']}\n";

if ($summary2['skipped_rows']) {
    echo "\n=== Détails des lignes ignorées ===\n";
    foreach ($summary2['skipped_rows'] as $row) {
        echo "Ligne {$row['row']}: champs manquants - " . implode(', ', $row['missing_fields']) . "\n";
    }
}

echo "\nTest terminé.\n";
