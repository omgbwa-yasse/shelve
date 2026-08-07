<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionUtilities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:utilities
                            {action : Action to perform (count|list|modules|check|stats)}
                            {--module= : Filter by module name}
                            {--search= : Search in permission names}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Utilities for managing and analyzing permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'count':
                $this->showCount();
                break;
            case 'list':
                $this->listPermissions();
                break;
            case 'modules':
                $this->showModules();
                break;
            case 'check':
                $this->checkIntegrity();
                break;
            case 'stats':
                $this->showStats();
                break;
            default:
                $this->error("Action inconnue: {$action}");
                $this->showHelp();
                return 1;
        }

        return 0;
    }

    /**
     * Show permissions count
     */
    private function showCount()
    {
        $count = Permission::count();
        $this->info("Total des permissions: {$count}");

        if ($count === 222) {
            $this->line('<fg=green>✅ Nombre de permissions correct (version complète)</fg=green>');
        } elseif ($count === 60) {
            $this->line('<fg=yellow>⚠️  Version de base détectée (60 permissions)</fg=yellow>');
        } else {
            $this->line('<fg=red>❌ Nombre de permissions inattendu</fg=red>');
        }
    }

    /**
     * List permissions with optional filtering
     */
    private function listPermissions()
    {
        $query = Permission::query();

        // Filter by module
        if ($module = $this->option('module')) {
            $query->where('name', 'like', $module . '_%');
        }

        // Search in names
        if ($search = $this->option('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $permissions = $query->orderBy('name')->get();

        if ($permissions->isEmpty()) {
            $this->warn('Aucune permission trouvée avec ces critères.');
            return;
        }

        $this->table(
            ['ID', 'Nom', 'Description'],
            $permissions->map(function ($permission) {
                return [
                    $permission->id,
                    $permission->name,
                    $permission->description ?? 'N/A'
                ];
            })
        );

        $this->info("Total affiché: {$permissions->count()} permissions");
    }

    /**
     * Show modules and their permission counts
     */
    private function showModules()
    {
        $permissions = Permission::all();

        $modules = $permissions->groupBy(function ($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'unknown';
        })->map(function ($group, $module) {
            return [
                'module' => $module,
                'count' => $group->count(),
                'permissions' => $group->pluck('name')->sort()->values()->toArray()
            ];
        })->sortBy('module');

        $this->table(
            ['Module', 'Permissions', 'Exemples'],
            $modules->map(function ($data) {
                return [
                    $data['module'],
                    $data['count'],
                    implode(', ', array_slice($data['permissions'], 0, 3)) .
                    ($data['count'] > 3 ? '...' : '')
                ];
            })
        );

        $this->info("Total modules: {$modules->count()}");
    }

    /**
     * Check permissions integrity
     */
    private function checkIntegrity()
    {
        $this->info('Vérification de l\'intégrité des permissions...');

        $this->checkDuplicates();
        $this->checkMissingStandardPermissions();
    }

    /**
     * Check for duplicate permissions
     */
    private function checkDuplicates()
    {
        $duplicates = Permission::select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->line('<fg=green>✅ Aucun doublon détecté</fg=green>');
        } else {
            $this->line('<fg=red>❌ Doublons détectés:</fg=red>');
            foreach ($duplicates as $duplicate) {
                $this->line("  - {$duplicate->name}");
            }
        }
    }

    /**
     * Check for missing standard permissions
     */
    private function checkMissingStandardPermissions()
    {
        $modules = Permission::all()->groupBy(function ($permission) {
            return explode('_', $permission->name)[0];
        });

        $standardActions = ['view', 'viewAny', 'create', 'update', 'delete', 'force_delete', 'restore'];
        $issues = $this->findMissingPermissions($modules, $standardActions);

        if (empty($issues)) {
            $this->line('<fg=green>✅ Structure des permissions cohérente</fg=green>');
        } else {
            $this->displayMissingPermissions($issues);
        }
    }

    /**
     * Find missing permissions for modules
     */
    private function findMissingPermissions($modules, $standardActions)
    {
        $issues = [];

        foreach ($modules as $module => $permissions) {
            if ($module === 'PublicPortal') {
                continue; // Skip special modules
            }

            $actions = $permissions->map(function ($permission) {
                $parts = explode('_', $permission->name);
                array_shift($parts);
                return implode('_', $parts);
            })->unique();

            foreach ($standardActions as $action) {
                if (!$actions->contains($action)) {
                    $issues[] = "{$module}_{$action}";
                }
            }
        }

        return $issues;
    }

    /**
     * Display missing permissions
     */
    private function displayMissingPermissions($issues)
    {
        $this->line('<fg=yellow>⚠️  Permissions manquantes potentielles:</fg=yellow>');
        foreach (array_slice($issues, 0, 10) as $issue) {
            $this->line("  - {$issue}");
        }
        if (count($issues) > 10) {
            $this->line("  ... et " . (count($issues) - 10) . " autres");
        }
    }

    /**
     * Show detailed statistics
     */
    private function showStats()
    {
        $permissions = Permission::all();
        $modules = $permissions->groupBy(function ($permission) {
            return explode('_', $permission->name)[0];
        });

        $this->info('📊 Statistiques détaillées des permissions');
        $this->line('');

        // General stats
        $this->line('<fg=cyan>Statistiques générales:</fg=cyan>');
        $this->line("• Total permissions: {$permissions->count()}");
        $this->line("• Total modules: {$modules->count()}");
        $this->line("• Moyenne par module: " . round($permissions->count() / $modules->count(), 1));
        $this->line('');

        // Top modules
        $this->line('<fg=cyan>Top 10 modules (par nombre de permissions):</fg=cyan>');
        $topModules = $modules->map(function ($perms, $module) {
            return ['module' => $module, 'count' => $perms->count()];
        })->sortByDesc('count')->take(10);

        foreach ($topModules as $data) {
            $bar = str_repeat('█', min(50, $data['count']));
            $this->line("• {$data['module']}: {$data['count']} {$bar}");
        }

        $this->line('');

        // Actions distribution
        $actions = $permissions->map(function ($permission) {
            $parts = explode('_', $permission->name);
            array_shift($parts);
            return implode('_', $parts);
        })->countBy();

        $this->line('<fg=cyan>Distribution des actions:</fg=cyan>');
        foreach ($actions->sortByDesc(function ($count) { return $count; })->take(10) as $action => $count) {
            $this->line("• {$action}: {$count}");
        }
    }

    /**
     * Show help information
     */
    private function showHelp()
    {
        $this->line('');
        $this->line('<fg=cyan>Actions disponibles:</fg=cyan>');
        $this->line('• <fg=green>count</fg=green>   - Affiche le nombre total de permissions');
        $this->line('• <fg=green>list</fg=green>    - Liste les permissions (avec filtres optionnels)');
        $this->line('• <fg=green>modules</fg=green> - Affiche les modules et leur nombre de permissions');
        $this->line('• <fg=green>check</fg=green>   - Vérifie l\'intégrité des permissions');
        $this->line('• <fg=green>stats</fg=green>   - Affiche des statistiques détaillées');
        $this->line('');
        $this->line('<fg=cyan>Options:</fg=cyan>');
        $this->line('• <fg=green>--module=NOM</fg=green>   - Filtre par module (pour list)');
        $this->line('• <fg=green>--search=TERME</fg=green>  - Recherche dans les noms (pour list)');
        $this->line('');
        $this->line('<fg=cyan>Exemples:</fg=cyan>');
        $this->line('• <fg=green>php artisan permissions:utilities count</fg=green>');
        $this->line('• <fg=green>php artisan permissions:utilities list --module=User</fg=green>');
        $this->line('• <fg=green>php artisan permissions:utilities list --search=create</fg=green>');
        $this->line('• <fg=green>php artisan permissions:utilities modules</fg=green>');
        $this->line('• <fg=green>php artisan permissions:utilities check</fg=green>');
        $this->line('• <fg=green>php artisan permissions:utilities stats</fg=green>');
    }
}
