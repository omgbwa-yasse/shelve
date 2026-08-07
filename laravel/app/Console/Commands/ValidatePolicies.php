<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ValidatePolicies extends Command
{
    protected $signature = 'policies:validate
                           {--fix : Attempt to fix common issues automatically}
                           {--detailed : Show detailed analysis}';

    protected $description = 'Validate policies and detect common issues';

    private array $issues = [];
    private array $suggestions = [];

    public function handle(): void
    {
        $this->info('🔍 Validation des policies...');

        $this->validatePolicyStructure();
        $this->validatePermissionNaming();
        $this->validateOrganisationChecks();
        $this->validateReturnTypes();
        $this->checkForCommonBugs();

        $this->displayResults();
    }

    private function validatePolicyStructure(): void
    {
        $policyPath = app_path('Policies');
        $files = File::files($policyPath);

        foreach ($files as $file) {
            $filename = $file->getFilename();

            if (!Str::endsWith($filename, 'Policy.php')) {
                continue;
            }

            $content = File::get($file->getPathname());
            $this->validateSinglePolicy($filename, $content);
        }
    }

    private function validateSinglePolicy(string $filename, string $content): void
    {
        $className = Str::before($filename, '.php');

        // Check if using base policy
        if (!Str::contains($content, 'extends BasePolicy') &&
            !Str::contains($content, 'extends PublicBasePolicy') &&
            !in_array($filename, ['BasePolicy.php', 'PublicBasePolicy.php', 'AdvancedRecordPolicy.php'])) {
            $this->issues[$filename][] = '❌ N\'utilise pas BasePolicy ou PublicBasePolicy';
            $this->suggestions[$filename][] = '💡 Migrer vers BasePolicy pour bénéficier des améliorations';
        }

        // Check for proper imports
        if (!Str::contains($content, 'use Illuminate\\Auth\\Access\\Response;') &&
            Str::contains($content, 'Response')) {
            $this->issues[$filename][] = '❌ Import Response manquant';
        }

        // Check for proper return types
        if (Str::contains($content, 'public function') &&
            !Str::contains($content, 'bool|Response') &&
            !Str::contains($content, ': bool') &&
            !in_array($filename, ['BasePolicy.php', 'PublicBasePolicy.php'])) {
            $this->issues[$filename][] = '⚠️  Types de retour potrebilement incorrects';
            $this->suggestions[$filename][] = '💡 Utiliser bool|Response pour les méthodes de policy';
        }
    }

    private function validatePermissionNaming(): void
    {
        $policyPath = app_path('Policies');
        $files = File::files($policyPath);

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $content = File::get($file->getPathname());

            // Extract permission names
            preg_match_all("/hasPermissionTo\('([^']+)'/", $content, $matches);
            $permissions = $matches[1] ?? [];

            foreach ($permissions as $permission) {
                if (!$this->isValidPermissionName($permission)) {
                    $this->issues[$filename][] = "❌ Nom de permission invalide: {$permission}";
                    $this->suggestions[$filename][] = "💡 Utiliser le format: model_action (ex: record_view)";
                }
            }
        }
    }

    private function validateOrganisationChecks(): void
    {
        $policyPath = app_path('Policies');
        $files = File::files($policyPath);

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $content = File::get($file->getPathname());

            // Skip public policies
            if (Str::startsWith($filename, 'Public')) {
                continue;
            }

            // Check for organisation access verification
            if (Str::contains($content, 'hasPermissionTo') &&
                !Str::contains($content, 'checkOrganisationAccess') &&
                !Str::contains($content, 'extends BasePolicy')) {
                $this->issues[$filename][] = '⚠️  Vérification d\'accès organisation manquante';
                $this->suggestions[$filename][] = '💡 Utiliser checkOrganisationAccess() ou migrer vers BasePolicy';
            }
        }
    }

    private function validateReturnTypes(): void
    {
        $policyPath = app_path('Policies');
        $files = File::files($policyPath);

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $content = File::get($file->getPathname());

            // Check for boolean-only returns where Response would be better
            if (preg_match_all('/public function \w+\([^)]+\): bool/', $content, $matches)) {
                foreach ($matches[0] as $match) {
                    if (!Str::contains($filename, 'Base')) {
                        $this->suggestions[$filename][] = "💡 Considérer bool|Response pour: {$match}";
                    }
                }
            }
        }
    }

    private function checkForCommonBugs(): void
    {
        $policyPath = app_path('Policies');
        $files = File::files($policyPath);

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $content = File::get($file->getPathname());

            // Check for the $record bug
            if (Str::contains($content, '$record') && !Str::contains($filename, 'Record')) {
                $this->issues[$filename][] = '🐛 Bug détecté: utilisation de $record au lieu du bon nom de variable';

                if ($this->option('fix')) {
                    $this->fixRecordBug($file->getPathname(), $content);
                }
            }

            // Check for missing null checks
            if (Str::contains($content, '$user->currentOrganisation') &&
                !Str::contains($content, 'if (!$user->currentOrganisation') &&
                !Str::contains($content, 'extends BasePolicy')) {
                $this->issues[$filename][] = '⚠️  Vérification null manquante pour currentOrganisation';
            }

            // Check for hardcoded true/false returns
            if (preg_match('/return true;.*\/\/ Default.*allow/', $content)) {
                $this->issues[$filename][] = '⚠️  Logique de sécurité : retour true par défaut détecté';
                $this->suggestions[$filename][] = '💡 Considérer un retour false par défaut pour plus de sécurité';
            }
        }
    }

    private function fixRecordBug(string $filePath, string $content): void
    {
        $filename = basename($filePath);
        $modelName = Str::before(Str::after($filename, ''), 'Policy.php');
        $correctVariable = '$' . Str::camel($modelName);

        $fixed = str_replace('$record', $correctVariable, $content);

        if ($fixed !== $content) {
            File::put($filePath, $fixed);
            $this->info("🔧 Corrigé automatiquement: {$filename}");
        }
    }

    private function isValidPermissionName(string $permission): bool
    {
        // Permission should follow pattern: model_action
        return preg_match('/^[a-z_]+_[a-z_]+$/', $permission) === 1;
    }

    private function displayResults(): void
    {
        $totalIssues = array_sum(array_map('count', $this->issues));
        $totalSuggestions = array_sum(array_map('count', $this->suggestions));

        if ($totalIssues === 0 && $totalSuggestions === 0) {
            $this->info('✅ Toutes les policies sont valides !');
            return;
        }

        $this->info("\n📋 Résultats de la validation:");

        foreach ($this->issues as $file => $fileIssues) {
            $this->line("\n📄 {$file}:");
            foreach ($fileIssues as $issue) {
                $this->line("  {$issue}");
            }
        }

        if ($this->option('detailed') && !empty($this->suggestions)) {
            $this->info("\n💡 Suggestions d'amélioration:");
            foreach ($this->suggestions as $file => $fileSuggestions) {
                $this->line("\n📄 {$file}:");
                foreach ($fileSuggestions as $suggestion) {
                    $this->line("  {$suggestion}");
                }
            }
        }

        $this->info("\n📊 Résumé:");
        $this->info("  ❌ Problèmes trouvés: {$totalIssues}");
        $this->info("  💡 Suggestions: {$totalSuggestions}");

        if ($totalIssues > 0) {
            $this->warn("\n⚠️  Résolvez les problèmes identifiés pour améliorer la sécurité.");
        }

        if ($this->option('fix')) {
            $this->info("\n🔧 Corrections automatiques appliquées quand possible.");
        } else {
            $this->info("\n💡 Utilisez --fix pour corriger automatiquement certains problèmes.");
        }
    }
}
