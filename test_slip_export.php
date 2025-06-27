<?php

require_once 'vendor/autoload.php';

// Test simple pour vérifier que les classes d'export existent et sont bien structurées

echo "Test de vérification de l'export des slips\n";
echo "=========================================\n\n";

// Vérifier que la classe SlipExport existe
if (class_exists('App\Exports\SlipExport')) {
    echo "✓ Classe SlipExport trouvée\n";
} else {
    echo "✗ Classe SlipExport introuvable\n";
}

// Vérifier que SlipsExport n'existe plus
if (!class_exists('App\Exports\SlipsExport')) {
    echo "✓ Classe SlipsExport correctement supprimée\n";
} else {
    echo "✗ Classe SlipsExport existe encore (devrait être supprimée)\n";
}

// Vérifier le contrôleur SlipController
$controllerPath = 'app/Http/Controllers/SlipController.php';
if (file_exists($controllerPath)) {
    $content = file_get_contents($controllerPath);

    // Vérifier que la méthode exportForm ne récupère plus les dollies
    if (strpos($content, '$dollies = Dolly::') === false && strpos($content, 'compact(\'dollies\'') === false) {
        echo "✓ Méthode exportForm nettoyée des références aux dollies\n";
    } else {
        echo "✗ Méthode exportForm contient encore des références aux dollies\n";
    }

    // Vérifier que la méthode export ne traite plus les dollies
    if (strpos($content, 'dollyId') === false || strpos($content, '$request->input(\'dolly_id\')') === false) {
        echo "✓ Méthode export nettoyée des références aux dollies\n";
    } else {
        echo "✗ Méthode export contient encore des références aux dollies\n";
    }
} else {
    echo "✗ Fichier SlipController introuvable\n";
}

// Vérifier la vue d'export
$viewPath = 'resources/views/slips/export.blade.php';
if (file_exists($viewPath)) {
    $content = file_get_contents($viewPath);

    // Vérifier que la vue ne référence plus les dollies
    if (strpos($content, 'dollies') === false && strpos($content, 'dolly_id') === false) {
        echo "✓ Vue d'export nettoyée des références aux dollies\n";
    } else {
        echo "✗ Vue d'export contient encore des références aux dollies\n";
    }

    // Vérifier que la vue propose la sélection d'un slip
    if (strpos($content, 'slip_id') !== false) {
        echo "✓ Vue d'export permet la sélection d'un slip spécifique\n";
    } else {
        echo "✗ Vue d'export ne permet pas la sélection d'un slip spécifique\n";
    }
} else {
    echo "✗ Fichier de vue d'export introuvable\n";
}

echo "\n";
echo "Test terminé\n";
echo "============\n";
echo "Points clés à vérifier :\n";
echo "- L'export ne traite qu'un seul bordereau à la fois\n";
echo "- L'export génère un fichier Excel avec deux onglets\n";
echo "- Seuls les bordereaux de l'organisation courante sont proposés\n";
echo "- Aucune référence aux dollies (chariots) dans l'export des slips\n";
