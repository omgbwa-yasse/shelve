<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test de l'autocomplétion
echo "=== Test d'autocomplétion des Records ===\n";

// Compter les records
$count = \App\Models\Record::count();
echo "Nombre de records dans la base: $count\n\n";

if ($count > 0) {
    echo "Premiers records:\n";
    $records = \App\Models\Record::take(5)->get(['id', 'name', 'code']);
    foreach ($records as $record) {
        echo "- ID: {$record->id}, Nom: {$record->name}, Code: {$record->code}\n";
    }

    echo "\n=== Test de recherche ===\n";
    $searchTerm = 'fac';
    echo "Recherche pour: '$searchTerm'\n";

    $results = \App\Models\Record::where(function($q) use ($searchTerm) {
        $q->where('name', 'LIKE', '%' . $searchTerm . '%')
          ->orWhere('code', 'LIKE', '%' . $searchTerm . '%');
    })
    ->select('id', 'name', 'code')
    ->limit(5)
    ->get();

    echo "Résultats trouvés: " . $results->count() . "\n";
    foreach ($results as $result) {
        echo "- {$result->name} ({$result->code})\n";
    }
} else {
    echo "Aucun record trouvé dans la base de données.\n";
}
?>
