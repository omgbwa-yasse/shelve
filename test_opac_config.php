<?php
// Script de test pour vérifier la nouvelle architecture OPAC

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\OpacConfiguration;
use App\Models\OpacConfigurationCategory;
use App\Models\Organisation;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test de la nouvelle architecture OPAC ===" . PHP_EOL . PHP_EOL;

// Test 1: Vérifier les catégories
echo "1. Catégories de configuration:" . PHP_EOL;
$categories = OpacConfigurationCategory::all();
foreach ($categories as $category) {
    echo "   - {$category->label} ({$category->name})" . PHP_EOL;
}
echo PHP_EOL;

// Test 2: Vérifier les configurations
echo "2. Configurations disponibles:" . PHP_EOL;
$configurations = OpacConfiguration::with('category')->get();
foreach ($configurations as $config) {
    echo "   - {$config->label} ({$config->key}) - Catégorie: {$config->category->label}" . PHP_EOL;
}
echo PHP_EOL;

// Test 3: Tester la récupération pour une organisation
echo "3. Test de configuration pour une organisation:" . PHP_EOL;
$organisation = Organisation::first();
if ($organisation) {
    echo "   Organisation: {$organisation->name}" . PHP_EOL;

    $configs = OpacConfiguration::getConfigurationsForOrganisation($organisation->id);
    echo "   Catégories avec configurations: " . count($configs) . PHP_EOL;

    foreach ($configs as $categoryName => $categoryConfigs) {
        echo "   - Catégorie {$categoryName}: " . count($categoryConfigs) . " configurations" . PHP_EOL;
    }
} else {
    echo "   Aucune organisation trouvée!" . PHP_EOL;
}
echo PHP_EOL;

// Test 4: Test des valeurs par défaut
echo "4. Test des valeurs par défaut:" . PHP_EOL;
$titleConfig = OpacConfiguration::where('key', 'opac_title')->first();
if ($titleConfig) {
    echo "   Configuration 'opac_title':" . PHP_EOL;
    echo "   - Valeur par défaut: {$titleConfig->default_value}" . PHP_EOL;
    echo "   - Type: {$titleConfig->type}" . PHP_EOL;

    if ($organisation) {
        $value = $titleConfig->getValueForOrganisation($organisation->id);
        echo "   - Valeur pour {$organisation->name}: {$value}" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Test terminé ===" . PHP_EOL;
?>
