<?php

namespace App\Services;

use App\Models\SystemVersion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Exception;
use ZipArchive;

class GitHubVersionService
{
    private GitHubApiService $githubApi;
    private string $versionFile;

    public function __construct(GitHubApiService $githubApi)
    {
        $this->githubApi = $githubApi;
        $this->versionFile = base_path('version.json');
    }

    /**
     * Get current version from version.json
     */
    public function getCurrentVersion(): ?string
    {
        if (!File::exists($this->versionFile)) {
            return null;
        }

        $versionData = json_decode(File::get($this->versionFile), true);
        return $versionData['current_version'] ?? null;
    }

    /**
     * Get version information
     */
    public function getVersionInfo(): array
    {
        if (!File::exists($this->versionFile)) {
            return [
                'current_version' => 'unknown',
                'installed_at' => null,
                'updated_from' => null
            ];
        }

        return json_decode(File::get($this->versionFile), true);
    }

    /**
     * Update version.json file
     */
    public function updateVersionFile(string $version, ?string $previousVersion = null): bool
    {
        try {
            $versionData = [
                'current_version' => $version,
                'installed_at' => now()->toISOString(),
                'updated_from' => $previousVersion,
                'github_repo' => 'omgbwa-yasse/shelve',
                'update_channel' => 'stable',
                'last_check' => now()->toISOString(),
                'build_number' => date('Ymd'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version()
            ];

            File::put($this->versionFile, json_encode($versionData, JSON_PRETTY_PRINT));
            return true;
        } catch (Exception $e) {
            Log::error('Failed to update version file', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get available versions from GitHub
     */
    public function getAvailableVersions(): array
    {
        $releases = $this->githubApi->getAllReleases();
        $currentVersion = $this->getCurrentVersion();

        $versions = [];

        foreach ($releases as $release) {
            $version = $release['tag_name'];

            $versions[] = [
                'tag_name' => $version,
                'name' => $release['name'],
                'body' => $release['body'],
                'published_at' => $release['published_at'],
                'prerelease' => $release['prerelease'],
                'draft' => $release['draft'],
                'download_url' => $release['zipball_url'],
                'is_newer' => $currentVersion ? $this->githubApi->isNewerVersion($version, $currentVersion) : true,
                'is_current' => $version === $currentVersion
            ];
        }

        return $versions;
    }

    /**
     * Get latest version
     */
    public function getLatestVersion(): ?array
    {
        $latest = $this->githubApi->getLatestRelease();

        if (!$latest) {
            return null;
        }

        $currentVersion = $this->getCurrentVersion();

        return [
            'tag_name' => $latest['tag_name'],
            'name' => $latest['name'],
            'body' => $latest['body'],
            'published_at' => $latest['published_at'],
            'is_newer' => $currentVersion ? $this->githubApi->isNewerVersion($latest['tag_name'], $currentVersion) : true
        ];
    }

    /**
     * Check for updates
     */
    public function checkForUpdates(): array
    {
        $currentVersion = $this->getCurrentVersion();

        if (!$currentVersion) {
            return [
                'has_updates' => false,
                'message' => 'Version actuelle non détectée'
            ];
        }

        $latest = $this->getLatestVersion();

        if (!$latest) {
            return [
                'has_updates' => false,
                'message' => 'Impossible de vérifier les mises à jour'
            ];
        }

        $hasUpdates = $latest['is_newer'];

        return [
            'has_updates' => $hasUpdates,
            'current_version' => $currentVersion,
            'latest_version' => $latest['tag_name'],
            'latest_info' => $latest,
            'message' => $hasUpdates ?
                "Nouvelle version disponible: {$latest['tag_name']}" :
                'Votre système est à jour'
        ];
    }

    /**
     * Download version from GitHub
     */
    public function downloadVersion(string $version): ?string
    {
        Log::info("Téléchargement de la version {$version}");

        $downloadPath = $this->githubApi->downloadRelease($version);

        if ($downloadPath && File::exists($downloadPath)) {
            Log::info("Version {$version} téléchargée avec succès", ['path' => $downloadPath]);
            return $downloadPath;
        }

        Log::error("Échec du téléchargement de la version {$version}");
        return null;
    }

    /**
     * Extract downloaded version
     */
    public function extractVersion(string $zipPath, string $targetPath): bool
    {
        try {
            $zip = new ZipArchive;

            if ($zip->open($zipPath) === true) {
                // Create target directory
                if (!File::exists($targetPath)) {
                    File::makeDirectory($targetPath, 0755, true);
                }

                $zip->extractTo($targetPath);
                $zip->close();

                Log::info("Version extraite avec succès", ['target' => $targetPath]);
                return true;
            }

            Log::error("Impossible d'ouvrir l'archive ZIP", ['path' => $zipPath]);
            return false;
        } catch (Exception $e) {
            Log::error("Erreur lors de l'extraction", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Validate version compatibility
     */
    public function validateVersion(): array
    {
        $errors = [];

        // Check PHP version compatibility
        if (version_compare(PHP_VERSION, '8.2.0', '<')) {
            $errors[] = 'PHP 8.2+ requis';
        }

        // Check disk space (need at least 500MB)
        $freeSpace = disk_free_space(base_path());
        if ($freeSpace < 500 * 1024 * 1024) {
            $errors[] = 'Espace disque insuffisant (500MB requis)';
        }

        // Check write permissions
        if (!is_writable(base_path())) {
            $errors[] = 'Permissions d\'écriture manquantes sur le répertoire racine';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Record version installation
     */
    public function recordInstallation(string $version, ?string $previousVersion = null, string $method = 'github'): void
    {
        try {
            SystemVersion::create([
                'version' => $version,
                'previous_version' => $previousVersion,
                'installed_at' => now(),
                'installation_method' => $method,
                'installed_by' => Auth::check() ? Auth::id() : null,
                'changelog' => $this->githubApi->getReleaseNotes($version)
            ]);

            Log::info("Installation de la version {$version} enregistrée");
        } catch (Exception $e) {
            Log::error("Erreur lors de l'enregistrement de l'installation", [
                'version' => $version,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get version history
     */
    public function getVersionHistory(): array
    {
        return SystemVersion::latest()
            ->with('installedBy')
            ->get()
            ->toArray();
    }

    /**
     * Update last check timestamp
     */
    public function updateLastCheck(): void
    {
        if (File::exists($this->versionFile)) {
            $versionData = json_decode(File::get($this->versionFile), true);
            $versionData['last_check'] = now()->toISOString();
            File::put($this->versionFile, json_encode($versionData, JSON_PRETTY_PRINT));
        }
    }
}
