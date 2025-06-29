#!/usr/bin/env php
<?php

/**
 * Script de migration automatique des Policies vers les bonnes pratiques Laravel
 */

$policiesDir = __DIR__ . '/app/Policies';
$excludeFiles = ['BasePolicy.php', 'PublicBasePolicy.php'];

// Liste des policies à migrer
$policyFiles = array_diff(
    array_filter(scandir($policiesDir), fn($file) => str_ends_with($file, '.php')),
    $excludeFiles
);

echo "🚀 Migration des Policies Laravel vers les bonnes pratiques\n";
echo "📁 Répertoire: {$policiesDir}\n";
echo "📋 Nombre de policies à migrer: " . count($policyFiles) . "\n\n";

foreach ($policyFiles as $file) {
    $filePath = $policiesDir . '/' . $file;

    if (!file_exists($filePath)) {
        continue;
    }

    echo "⚙️  Migration de {$file}...\n";

    $content = file_get_contents($filePath);
    $originalContent = $content;

    // 1. Ajouter le support des Guest Users (User optionnel)
    $patterns = [
        // ViewAny
        '/public function viewAny\(User \$user\): bool\|Response/' => 'public function viewAny(?User $user): bool|Response',

        // View avec modèle
        '/public function view\(User \$user, ([A-Z][a-zA-Z]*) \$([a-zA-Z]*)\): bool\|Response/' => 'public function view(?User $user, $1 $$2): bool|Response',

        // Create
        '/public function create\(User \$user\): bool\|Response/' => 'public function create(?User $user): bool|Response',

        // Update avec modèle
        '/public function update\(User \$user, ([A-Z][a-zA-Z]*) \$([a-zA-Z]*)\): bool\|Response/' => 'public function update(?User $user, $1 $$2): bool|Response',

        // Delete avec modèle
        '/public function delete\(User \$user, ([A-Z][a-zA-Z]*) \$([a-zA-Z]*)\): bool\|Response/' => 'public function delete(?User $user, $1 $$2): bool|Response',

        // Restore avec modèle
        '/public function restore\(User \$user, ([A-Z][a-zA-Z]*) \$([a-zA-Z]*)\): bool\|Response/' => 'public function restore(?User $user, $1 $$2): bool|Response',

        // ForceDelete avec modèle
        '/public function forceDelete\(User \$user, ([A-Z][a-zA-Z]*) \$([a-zA-Z]*)\): bool\|Response/' => 'public function forceDelete(?User $user, $1 $$2): bool|Response',
    ];

    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }

    // 2. Ajouter les commentaires de documentation
    $docPatterns = [
        '/(\s+\/\*\*\s+\* Determine whether the user can view any models\.\s+\*\/)/' => '$1' . "\n     * Supports guest users with optional type-hint.",
        '/(\s+\/\*\*\s+\* Determine whether the user can view the model\.\s+\*\/)/' => '$1' . "\n     * Supports guest users with optional type-hint.",
        '/(\s+\/\*\*\s+\* Determine whether the user can create models\.\s+\*\/)/' => '$1' . "\n     * Supports guest users with optional type-hint.",
        '/(\s+\/\*\*\s+\* Determine whether the user can update the model\.\s+\*\/)/' => '$1' . "\n     * Supports guest users with optional type-hint.",
        '/(\s+\/\*\*\s+\* Determine whether the user can delete the model\.\s+\*\/)/' => '$1' . "\n     * Supports guest users with optional type-hint.",
        '/(\s+\/\*\*\s+\* Determine whether the user can restore the model\.\s+\*\/)/' => '$1' . "\n     * Supports guest users with optional type-hint.",
        '/(\s+\/\*\*\s+\* Determine whether the user can permanently delete the model\.\s+\*\/)/' => '$1' . "\n     * Supports guest users with optional type-hint.",
    ];

    foreach ($docPatterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }

    // 3. Supprimer les méthodes checkOrganisationAccess dupliquées
    $content = preg_replace(
        '/\s+\/\*\*\s+\* Check if the user has access to the model within their current organisation\.\s+\*\/\s+private function checkOrganisationAccess\([^}]+}\s+}\s+}/s',
        '',
        $content
    );

    // Sauvegarder seulement si des changements ont été effectués
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "✅ {$file} mis à jour\n";
    } else {
        echo "⏭️  {$file} déjà à jour\n";
    }
}

echo "\n🎉 Migration terminée !\n";
echo "💡 N'oubliez pas de :\n";
echo "   - Tester vos policies\n";
echo "   - Vérifier les permissions Guest selon vos besoins\n";
echo "   - Utiliser @can/@endcan dans vos templates Blade\n";
