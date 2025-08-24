<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateService
{
    private GitHubVersionService $versionService;
    private string $backupPath;
    private string $tempPath;

    public function __construct(GitHubVersionService $versionService)
    {
        $this->versionService = $versionService;
        $this->backupPath = storage_path('app/backups');
        $this->tempPath = storage_path('app/updates');
    }

    /**
     * Prepare update process
     */
    public function prepareUpdate(string $version): array
    {
        Log::info("Préparation de la mise à jour vers {$version}");

        // Validate environment
        $validation = $this->versionService->validateVersion();
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validation['errors']
            ];
        }

        // Create necessary directories
        $this->createDirectories();

        return [
            'success' => true,
            'message' => 'Préparation terminée'
        ];
    }

    /**
     * Create backup of current installation
     */
    public function createBackup(): array
    {
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $currentVersion = $this->versionService->getCurrentVersion() ?? 'unknown';
            $backupDir = $this->backupPath . "/backup_{$currentVersion}_{$timestamp}";

            Log::info("Création de la sauvegarde dans {$backupDir}");

            // Create backup directory
            File::makeDirectory($backupDir, 0755, true);

            // Backup critical files and directories
            $itemsToBackup = [
                'app',
                'bootstrap',
                'config',
                'database',
                'resources',
                'routes',
                'public',
                'composer.json',
                'composer.lock',
                'artisan',
                'version.json',
                '.env'
            ];

            foreach ($itemsToBackup as $item) {
                $sourcePath = base_path($item);
                $targetPath = $backupDir . '/' . $item;

                if (File::exists($sourcePath)) {
                    if (File::isDirectory($sourcePath)) {
                        File::copyDirectory($sourcePath, $targetPath);
                    } else {
                        File::copy($sourcePath, $targetPath);
                    }
                }
            }

            Log::info("Sauvegarde créée avec succès");

            return [
                'success' => true,
                'backup_path' => $backupDir,
                'message' => 'Sauvegarde créée'
            ];
        } catch (Exception $e) {
            Log::error("Erreur lors de la sauvegarde", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Download and extract new version
     */
    public function downloadAndExtract(string $version): array
    {
        try {
            // Download version
            $downloadPath = $this->versionService->downloadVersion($version);
            if (!$downloadPath) {
                return [
                    'success' => false,
                    'message' => 'Échec du téléchargement'
                ];
            }

            // Extract to temporary directory
            $extractPath = $this->tempPath . "/extract_{$version}";
            if (!$this->versionService->extractVersion($downloadPath, $extractPath)) {
                return [
                    'success' => false,
                    'message' => 'Échec de l\'extraction'
                ];
            }

            // Find the extracted directory (GitHub creates a directory with repo name)
            $extractedDirs = File::directories($extractPath);
            if (empty($extractedDirs)) {
                return [
                    'success' => false,
                    'message' => 'Répertoire extrait non trouvé'
                ];
            }

            $sourceDir = $extractedDirs[0];

            return [
                'success' => true,
                'source_path' => $sourceDir,
                'extract_path' => $extractPath,
                'message' => 'Téléchargement et extraction terminés'
            ];
        } catch (Exception $e) {
            Log::error("Erreur lors du téléchargement", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Apply the update
     */
    public function applyUpdate(string $sourcePath, string $version): array
    {
        try {
            Log::info("Application de la mise à jour depuis {$sourcePath}");

            // Put application in maintenance mode
            Artisan::call('down', ['--message' => "Mise à jour vers {$version} en cours..."]);

            // Update files
            $this->updateFiles($sourcePath);

            // Update composer dependencies
            $this->updateComposerDependencies();

            // Run database migrations
            $this->runMigrations();

            // Clear and optimize caches
            $this->clearAndOptimize();

            // Update version file
            $currentVersion = $this->versionService->getCurrentVersion();
            $this->versionService->updateVersionFile($version, $currentVersion);

            // Record installation
            $this->versionService->recordInstallation($version, $currentVersion, 'github');

            // Take application out of maintenance mode
            Artisan::call('up');

            Log::info("Mise à jour vers {$version} terminée avec succès");

            return [
                'success' => true,
                'message' => "Mise à jour vers {$version} réussie"
            ];
        } catch (Exception $e) {
            // Ensure app is back up in case of error
            Artisan::call('up');

            Log::error("Erreur lors de l'application de la mise à jour", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update application files
     */
    private function updateFiles(string $sourcePath): void
    {
        $filesToUpdate = [
            'app',
            'bootstrap',
            'config',
            'database',
            'resources',
            'routes',
            'public',
            'composer.json',
            'composer.lock',
            'artisan'
        ];

        foreach ($filesToUpdate as $item) {
            $sourcePath_ = $sourcePath . '/' . $item;
            $targetPath = base_path($item);

            if (File::exists($sourcePath_)) {
                // Remove existing file/directory
                if (File::exists($targetPath)) {
                    if (File::isDirectory($targetPath)) {
                        File::deleteDirectory($targetPath);
                    } else {
                        File::delete($targetPath);
                    }
                }

                // Copy new file/directory
                if (File::isDirectory($sourcePath_)) {
                    File::copyDirectory($sourcePath_, $targetPath);
                } else {
                    File::copy($sourcePath_, $targetPath);
                }

                Log::info("Fichier mis à jour: {$item}");
            }
        }
    }

    /**
     * Update composer dependencies
     */
    private function updateComposerDependencies(): void
    {
        Log::info("Mise à jour des dépendances Composer");

        // Install/update composer dependencies
        exec('cd ' . base_path() . ' && composer install --optimize-autoloader --no-dev', $output, $returnCode);

        if ($returnCode !== 0) {
            Log::warning("Composer install returned non-zero exit code", ['output' => $output]);
        }
    }

    /**
     * Run database migrations
     */
    private function runMigrations(): void
    {
        Log::info("Exécution des migrations de base de données");
        Artisan::call('migrate', ['--force' => true]);
    }

    /**
     * Clear and optimize caches
     */
    private function clearAndOptimize(): void
    {
        Log::info("Nettoyage et optimisation des caches");

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
    }

    /**
     * Rollback to previous version
     */
    public function rollback(string $backupPath): array
    {
        try {
            Log::info("Rollback vers la sauvegarde {$backupPath}");

            if (!File::exists($backupPath)) {
                return [
                    'success' => false,
                    'message' => 'Sauvegarde non trouvée'
                ];
            }

            // Put in maintenance mode
            Artisan::call('down', ['--message' => 'Rollback en cours...']);

            // Restore files
            $this->restoreFiles($backupPath);

            // Restore version file
            if (File::exists($backupPath . '/version.json')) {
                File::copy($backupPath . '/version.json', base_path('version.json'));
            }

            // Clear caches
            $this->clearAndOptimize();

            // Take out of maintenance mode
            Artisan::call('up');

            Log::info("Rollback terminé avec succès");

            return [
                'success' => true,
                'message' => 'Rollback réussi'
            ];
        } catch (Exception $e) {
            Artisan::call('up');
            Log::error("Erreur lors du rollback", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Erreur lors du rollback: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Restore files from backup
     */
    private function restoreFiles(string $backupPath): void
    {
        $itemsToRestore = [
            'app',
            'bootstrap',
            'config',
            'database',
            'resources',
            'routes',
            'public',
            'composer.json',
            'composer.lock',
            'artisan'
        ];

        foreach ($itemsToRestore as $item) {
            $sourcePath = $backupPath . '/' . $item;
            $targetPath = base_path($item);

            if (File::exists($sourcePath)) {
                // Remove current file/directory
                if (File::exists($targetPath)) {
                    if (File::isDirectory($targetPath)) {
                        File::deleteDirectory($targetPath);
                    } else {
                        File::delete($targetPath);
                    }
                }

                // Restore from backup
                if (File::isDirectory($sourcePath)) {
                    File::copyDirectory($sourcePath, $targetPath);
                } else {
                    File::copy($sourcePath, $targetPath);
                }
            }
        }
    }

    /**
     * Create necessary directories
     */
    private function createDirectories(): void
    {
        $directories = [
            $this->backupPath,
            $this->tempPath
        ];

        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }
    }

    /**
     * Clean up temporary files
     */
    public function cleanup(): void
    {
        try {
            if (File::exists($this->tempPath)) {
                File::deleteDirectory($this->tempPath);
                File::makeDirectory($this->tempPath, 0755, true);
            }
            Log::info("Nettoyage des fichiers temporaires terminé");
        } catch (Exception $e) {
            Log::error("Erreur lors du nettoyage", ['error' => $e->getMessage()]);
        }
    }
}
