<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Exception;

class GitUpdateService
{
    /**
     * Perform the update process
     */
    public function update(bool $force = false): array
    {
        try {
            $steps = [];

            // Step 1: Git Pull
            $gitPull = $this->gitPull($force);
            $steps[] = $gitPull;
            if (!$gitPull['success']) {
                return ['success' => false, 'message' => $gitPull['message'], 'steps' => $steps];
            }

            // Step 2: Migrations
            $migrations = $this->runMigrations();
            $steps[] = $migrations;
            if (!$migrations['success']) {
                return ['success' => false, 'message' => $migrations['message'], 'steps' => $steps];
            }

            // Step 3: Clear Cache
            $cache = $this->clearCache();
            $steps[] = $cache;

            return [
                'success' => true,
                'message' => 'Mise à jour terminée avec succès',
                'steps' => $steps
            ];

        } catch (Exception $e) {
            Log::error('GitUpdateService error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
                'steps' => $steps ?? []
            ];
        }
    }

    /**
     * Pull latest changes from git
     */
    private function gitPull(bool $force = false): array
    {
        Log::info('GitUpdateService: Starting git pull');
        
        $command = 'git pull origin main';
        if ($force) {
            // Discard local changes and pull
            exec('git reset --hard HEAD', $resetOutput, $resetStatus);
            if ($resetStatus !== 0) {
                return ['success' => false, 'message' => 'Échec du reset git: ' . implode("\n", $resetOutput)];
            }
        }

        exec($command . ' 2>&1', $output, $status);

        if ($status !== 0) {
            Log::error('Git pull failed', ['output' => $output]);
            return [
                'success' => false,
                'message' => 'Échec du git pull. Il y a peut-être des conflits locaux.',
                'output' => $output
            ];
        }

        return [
            'success' => true,
            'message' => 'Git pull réussi',
            'output' => $output
        ];
    }

    /**
     * Run database migrations
     */
    private function runMigrations(): array
    {
        Log::info('GitUpdateService: Running migrations');
        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return [
                'success' => $exitCode === 0,
                'message' => $exitCode === 0 ? 'Migrations réussies' : 'Échec des migrations',
                'output' => $output
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors des migrations: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clear application cache
     */
    private function clearCache(): array
    {
        Log::info('GitUpdateService: Clearing cache');
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            // Re-cache if possible
            Artisan::call('config:cache');

            return [
                'success' => true,
                'message' => 'Caches nettoyés'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du nettoyage du cache: ' . $e->getMessage()
            ];
        }
    }
}
