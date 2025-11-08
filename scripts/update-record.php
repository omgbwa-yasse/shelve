<?php
/**
 * Script de migration: Record → RecordPhysical
 * Ce script remplace toutes les références au modèle Record par RecordPhysical
 */

$dryRun = in_array('--dry-run', $argv);
$rootPath = __DIR__ . '/..';

$stats = [
    'scanned' => 0,
    'modified' => 0,
    'replacements' => 0
];

// Répertoires à traiter
$directories = [
    'app/Http/Controllers',
    'app/Services',
    'app/Policies',
    'app/Observers',
    'app/Jobs',
    'app/Models',
    'app/Imports',
    'app/Exports',
    'app/Console/Commands',
    'app/Providers'
];

// Fichiers à exclure
$excludeFiles = ['Record.php', 'RecordPhysical.php', 'SlipRecord.php', 'PublicRecord.php', 'MailRecord.php'];

function processFile($filePath, $dryRun, &$stats) {
    global $excludeFiles, $rootPath;

    $filename = basename($filePath);
    if (in_array($filename, $excludeFiles)) {
        return;
    }

    $stats['scanned']++;
    $content = file_get_contents($filePath);
    $original = $content;

    // Remplacement 1: Import statement
    $content = str_replace('use App\Models\Record;', 'use App\Models\RecordPhysical;', $content);

    // Remplacement 2: Type hints et return types
    $content = preg_replace('/\bRecord\s+\$/', 'RecordPhysical $', $content);
    $content = preg_replace('/:\s*Record\b/', ': RecordPhysical', $content);

    // Remplacement 3: Static calls (mais pas SlipRecord::, PublicRecord::, etc.)
    $content = preg_replace('/(?<!Slip)(?<!Public)(?<!Mail)Record::/', 'RecordPhysical::', $content);

    // Remplacement 4: Class references
    $content = preg_replace('/(?<!Slip)(?<!Public)(?<!Mail)Record::class/', 'RecordPhysical::class', $content);
    $content = str_replace('\App\Models\Record::class', '\App\Models\RecordPhysical::class', $content);

    if ($content !== $original) {
        $stats['modified']++;
        $stats['replacements']++;

        $relativePath = str_replace($rootPath . '/', '', $filePath);
        echo "  [MODIFIE] {$relativePath}\n";

        if (!$dryRun) {
            file_put_contents($filePath, $content);
        }
    }
}

function scanDirectory($directory, $dryRun, &$stats) {
    global $rootPath;

    $path = $rootPath . '/' . $directory;
    if (!is_dir($path)) {
        return;
    }

    echo "\nTraitement: {$directory}\n";

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            processFile($file->getPathname(), $dryRun, $stats);
        }
    }
}

// Début du script
echo "\n========================================\n";
echo "  MIGRATION: Record -> RecordPhysical\n";
echo "========================================\n";

if ($dryRun) {
    echo "\n[MODE SIMULATION - Aucun fichier ne sera modifie]\n";
}

foreach ($directories as $directory) {
    scanDirectory($directory, $dryRun, $stats);
}

// Résumé
echo "\n========================================\n";
echo "  RESUME\n";
echo "========================================\n";
echo "Fichiers analyses      : {$stats['scanned']}\n";
echo "Fichiers modifies      : {$stats['modified']}\n";
echo "Remplacements effectues: {$stats['replacements']}\n";

if ($dryRun) {
    echo "\n[Pour appliquer les changements, executez sans --dry-run]\n";
} else {
    echo "\n[TERMINE - Executez: composer dump-autoload]\n";
}

echo "\n";
