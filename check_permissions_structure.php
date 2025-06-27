<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $structure = DB::select('DESCRIBE permissions');
    echo "Structure de la table permissions:\n";
    foreach($structure as $column) {
        echo "- {$column->Field}: {$column->Type}\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
