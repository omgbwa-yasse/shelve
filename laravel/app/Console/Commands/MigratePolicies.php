<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigratePolicies extends Command
{
    protected $signature = 'policies:migrate
                           {--dry-run : Show what would be changed without making changes}
                           {--force : Force migration even if backup exists}';

    protected $description = 'Migrate existing policies to use BasePolicy';

    private array $policyFiles = [];
    private array $publicPolicyFiles = [];
    private array $results = [];

    public function handle(): void
    {
        $this->info('🔄 Démarrage de la migration des policies...');

        $this->loadPolicyFiles();

        if ($this->option('dry-run')) {
            $this->info('🔍 Mode dry-run activé - Aucune modification ne sera effectuée');
        }

        $this->migratePolicies();
        $this->displayResults();
    }

    private function loadPolicyFiles(): void
    {
        $policyPath = app_path('Policies');
        $files = File::files($policyPath);

        foreach ($files as $file) {
            $filename = $file->getFilename();

            if (!Str::endsWith($filename, 'Policy.php')) {
                continue;
            }

            if (in_array($filename, ['BasePolicy.php', 'PublicBasePolicy.php', 'AdvancedRecordPolicy.php'])) {
                continue; // Skip our base classes
            }

            if (Str::startsWith($filename, 'Public')) {
                $this->publicPolicyFiles[] = $file->getPathname();
            } else {
                $this->policyFiles[] = $file->getPathname();
            }
        }

        $this->info("📁 Trouvé " . count($this->policyFiles) . " policies standard et " . count($this->publicPolicyFiles) . " policies publiques");
    }

    private function migratePolicies(): void
    {
        // Migrate standard policies
        foreach ($this->policyFiles as $filePath) {
            $this->migrateStandardPolicy($filePath);
        }

        // Migrate public policies
        foreach ($this->publicPolicyFiles as $filePath) {
            $this->migratePublicPolicy($filePath);
        }
    }

    private function migrateStandardPolicy(string $filePath): void
    {
        $filename = basename($filePath);
        $className = Str::before($filename, '.php');
        $modelName = Str::before($className, 'Policy');

        $this->info("🔄 Migration de {$filename}...");

        $content = File::get($filePath);
        $originalContent = $content;

        // Check if already using BasePolicy
        if (Str::contains($content, 'extends BasePolicy')) {
            $this->results[$filename] = '✅ Déjà migré';
            return;
        }

        // Create backup
        if (!$this->option('dry-run')) {
            File::put($filePath . '.backup', $content);
        }

        try {
            $newContent = $this->generateStandardPolicyContent($className, $modelName, $content);

            if ($this->option('dry-run')) {
                $this->line("📋 Aperçu des changements pour {$filename}:");
                $this->line($this->getContentDiff($content, $newContent));
            } else {
                File::put($filePath, $newContent);
                $this->results[$filename] = '✅ Migré avec succès';
            }
        } catch (\Exception $e) {
            $this->results[$filename] = '❌ Erreur: ' . $e->getMessage();
        }
    }

    private function migratePublicPolicy(string $filePath): void
    {
        $filename = basename($filePath);
        $className = Str::before($filename, '.php');

        $this->info("🔄 Migration de {$filename} (Public)...");

        $content = File::get($filePath);

        // Check if already using PublicBasePolicy
        if (Str::contains($content, 'extends PublicBasePolicy')) {
            $this->results[$filename] = '✅ Déjà migré (Public)';
            return;
        }

        // Create backup
        if (!$this->option('dry-run')) {
            File::put($filePath . '.backup', $content);
        }

        try {
            $newContent = $this->generatePublicPolicyContent($className, $content);

            if ($this->option('dry-run')) {
                $this->line("📋 Aperçu des changements pour {$filename}:");
                $this->line($this->getContentDiff($content, $newContent));
            } else {
                File::put($filePath, $newContent);
                $this->results[$filename] = '✅ Migré avec succès (Public)';
            }
        } catch (\Exception $e) {
            $this->results[$filename] = '❌ Erreur: ' . $e->getMessage();
        }
    }

    private function generateStandardPolicyContent(string $className, string $modelName, string $originalContent): string
    {
        $modelVariable = Str::camel($modelName);
        $permissionPrefix = Str::snake($modelName);

        // Extract namespace
        preg_match('/namespace\s+([^;]+);/', $originalContent, $namespaceMatch);
        $namespace = $namespaceMatch[1] ?? 'App\\Policies';

        // Check if it has the $record bug
        $hasRecordBug = Str::contains($originalContent, '$record');

        $content = "<?php

namespace {$namespace};

use App\\Models\\{$modelName};
use App\\Models\\User;
use Illuminate\\Auth\\Access\\Response;

class {$className} extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User \$user): bool|Response
    {
        return \$this->canViewAny(\$user, '{$permissionPrefix}_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User \$user, {$modelName} \${$modelVariable}): bool|Response
    {
        return \$this->canView(\$user, \${$modelVariable}, '{$permissionPrefix}_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User \$user): bool|Response
    {
        return \$this->canCreate(\$user, '{$permissionPrefix}_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User \$user, {$modelName} \${$modelVariable}): bool|Response
    {
        return \$this->canUpdate(\$user, \${$modelVariable}, '{$permissionPrefix}_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User \$user, {$modelName} \${$modelVariable}): bool|Response
    {
        return \$this->canDelete(\$user, \${$modelVariable}, '{$permissionPrefix}_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User \$user, {$modelName} \${$modelVariable}): bool|Response
    {
        return \$this->canForceDelete(\$user, \${$modelVariable}, '{$permissionPrefix}_force_delete');
    }
}
";

        return $content;
    }

    private function generatePublicPolicyContent(string $className, string $originalContent): string
    {
        // For public policies, we need to analyze the existing content more carefully
        // This is a simplified version - in practice, you'd want more sophisticated parsing

        preg_match('/namespace\s+([^;]+);/', $originalContent, $namespaceMatch);
        $namespace = $namespaceMatch[1] ?? 'App\\Policies';

        // Extract use statements
        preg_match_all('/use\s+([^;]+);/', $originalContent, $useMatches);
        $uses = $useMatches[1] ?? [];

        $useStatements = '';
        foreach ($uses as $use) {
            if (!Str::contains($use, 'HandlesAuthorization')) {
                $useStatements .= "use {$use};\n";
            }
        }

        return "<?php

namespace {$namespace};

{$useStatements}use Illuminate\\Auth\\Access\\Response;

class {$className} extends PublicBasePolicy
{
    // TODO: Implement specific methods based on original policy
    // This is a template - you'll need to customize based on the original policy
}
";
    }

    private function getContentDiff(string $original, string $new): string
    {
        $originalLines = explode("\n", $original);
        $newLines = explode("\n", $new);

        return "📊 Lignes originales: " . count($originalLines) . " -> Nouvelles lignes: " . count($newLines);
    }

    private function displayResults(): void
    {
        $this->info("\n📋 Résultats de la migration:");

        $successful = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($this->results as $file => $result) {
            $this->line("  {$file}: {$result}");

            if (Str::contains($result, '✅')) {
                $successful++;
            } elseif (Str::contains($result, '❌')) {
                $failed++;
            } else {
                $skipped++;
            }
        }

        $this->info("\n📊 Résumé:");
        $this->info("  ✅ Réussies: {$successful}");
        $this->info("  ❌ Échouées: {$failed}");
        $this->info("  ⏭️  Ignorées: {$skipped}");

        if ($failed > 0) {
            $this->warn("\n⚠️  Certaines migrations ont échoué. Vérifiez les logs ci-dessus.");
        }

        if (!$this->option('dry-run') && $successful > 0) {
            $this->info("\n💾 Des sauvegardes ont été créées avec l'extension .backup");
            $this->info("🔄 N'oubliez pas de tester vos policies après la migration!");
        }
    }
}
