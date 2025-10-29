<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Author;
use App\Models\Record;
use App\Models\PublicRecord;

try {
    // Créer quelques auteurs
    $author1 = Author::firstOrCreate(['name' => 'Jean Dupont'], ['type_id' => 1, 'lifespan' => '1980-']);
    $author2 = Author::firstOrCreate(['name' => 'Marie Martin'], ['type_id' => 1, 'lifespan' => '1975-']);
    $author3 = Author::firstOrCreate(['name' => 'Pierre Durand'], ['type_id' => 1, 'lifespan' => '1970-']);

    echo "Auteurs créés:\n";
    echo "- {$author1->name} (ID: {$author1->id})\n";
    echo "- {$author2->name} (ID: {$author2->id})\n";
    echo "- {$author3->name} (ID: {$author3->id})\n\n";

    // Associer les auteurs aux records existants
    $records = Record::limit(3)->get();
    $authors = [$author1, $author2, $author3];

    foreach($records as $i => $record) {
        if(isset($authors[$i])) {
            $record->authors()->sync([$authors[$i]->id]);
            echo "Auteur {$authors[$i]->name} associé au record '{$record->name}' (ID: {$record->id})\n";
        }
    }

    echo "\nAuteurs créés et associés avec succès!\n";

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
