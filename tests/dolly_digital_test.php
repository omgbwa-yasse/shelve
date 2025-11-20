<?php

/**
 * Script de test rapide pour le système Dolly Digital
 *
 * Exécuter avec: php artisan tinker
 * Puis: include 'tests/dolly_digital_test.php';
 */

use App\Models\Dolly;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use App\Models\RecordArtifact;
use App\Models\RecordBook;
use App\Models\RecordBookPublisherSeries;
use Illuminate\Support\Facades\Auth;

echo "🧪 TEST DOLLY DIGITAL SYSTEM\n";
echo "=============================\n\n";

// Test 1: Vérifier les modèles
echo "1️⃣ Test des modèles...\n";
try {
    $dollyCount = Dolly::count();
    $folderCount = RecordDigitalFolder::count();
    $documentCount = RecordDigitalDocument::count();
    $artifactCount = RecordArtifact::count();
    $bookCount = RecordBook::count();
    $seriesCount = RecordBookPublisherSeries::count();

    echo "   ✅ Dolly: {$dollyCount} enregistrements\n";
    echo "   ✅ Digital Folders: {$folderCount} enregistrements\n";
    echo "   ✅ Digital Documents: {$documentCount} enregistrements\n";
    echo "   ✅ Artifacts: {$artifactCount} enregistrements\n";
    echo "   ✅ Books: {$bookCount} enregistrements\n";
    echo "   ✅ Book Series: {$seriesCount} enregistrements\n";
} catch (\Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Test 2: Vérifier les relations
echo "\n2️⃣ Test des relations Dolly...\n";
try {
    $dolly = Dolly::first();
    if ($dolly) {
        $hasDigitalFolders = method_exists($dolly, 'digitalFolders');
        $hasDigitalDocuments = method_exists($dolly, 'digitalDocuments');
        $hasArtifacts = method_exists($dolly, 'artifacts');
        $hasBooks = method_exists($dolly, 'books');
        $hasBookSeries = method_exists($dolly, 'bookSeries');

        echo "   " . ($hasDigitalFolders ? "✅" : "❌") . " digitalFolders()\n";
        echo "   " . ($hasDigitalDocuments ? "✅" : "❌") . " digitalDocuments()\n";
        echo "   " . ($hasArtifacts ? "✅" : "❌") . " artifacts()\n";
        echo "   " . ($hasBooks ? "✅" : "❌") . " books()\n";
        echo "   " . ($hasBookSeries ? "✅" : "❌") . " bookSeries()\n";
    } else {
        echo "   ⚠️ Aucun dolly trouvé dans la base\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Test 3: Vérifier les catégories
echo "\n3️⃣ Test des catégories...\n";
try {
    $categories = Dolly::categories();
    $expectedNew = ['digital_folder', 'digital_document', 'artifact', 'book', 'book_series'];

    foreach ($expectedNew as $cat) {
        $exists = $categories->contains($cat);
        echo "   " . ($exists ? "✅" : "❌") . " {$cat}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Test 4: Vérifier les tables pivot
echo "\n4️⃣ Test des tables pivot...\n";
try {
    $tables = [
        'dolly_digital_folders',
        'dolly_digital_documents',
        'dolly_artifacts',
        'dolly_books',
        'dolly_book_series'
    ];

    foreach ($tables as $table) {
        $exists = \Schema::hasTable($table);
        echo "   " . ($exists ? "✅" : "❌") . " {$table}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Test 5: Vérifier les routes
echo "\n5️⃣ Test des routes...\n";
try {
    $routes = [
        'dolly.add-digital-folder',
        'dolly.remove-digital-folder',
        'dolly.add-digital-document',
        'dolly.remove-digital-document',
        'dolly.add-artifact',
        'dolly.remove-artifact',
        'dolly.add-book',
        'dolly.remove-book',
        'dolly.add-book-series',
        'dolly.remove-book-series',
        'dollies.action'
    ];

    foreach ($routes as $routeName) {
        $exists = \Route::has($routeName);
        echo "   " . ($exists ? "✅" : "❌") . " {$routeName}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Test 6: Vérifier les vues
echo "\n6️⃣ Test des vues...\n";
try {
    $views = [
        'dollies.exports.digital_folders_inventory',
        'dollies.exports.digital_documents_inventory',
        'dollies.exports.artifacts_inventory',
        'dollies.exports.books_inventory',
        'dollies.exports.book_series_inventory',
        'dollies.imports.book_import_isbd',
        'dollies.imports.book_import_marc',
        'dollies.imports.book_series_import_isbd',
        'dollies.imports.book_series_import_marc'
    ];

    foreach ($views as $viewName) {
        $exists = \View::exists($viewName);
        echo "   " . ($exists ? "✅" : "❌") . " {$viewName}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Test 7: Vérifier les méthodes du contrôleur
echo "\n7️⃣ Test des méthodes DollyActionController...\n";
try {
    $controller = new \App\Http\Controllers\DollyActionController();
    $methods = [
        'digitalFolderExportSeda',
        'digitalFolderExportInventory',
        'digitalDocumentExportSeda',
        'digitalDocumentExportInventory',
        'artifactExportInventory',
        'bookExportInventory',
        'bookExportISBD',
        'bookExportMARC',
        'bookSeriesExportInventory',
        'bookSeriesExportISBD',
        'bookSeriesExportMARC'
    ];

    foreach ($methods as $method) {
        $exists = method_exists($controller, $method);
        echo "   " . ($exists ? "✅" : "❌") . " {$method}()\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=============================\n";
echo "✨ Tests terminés !\n";
