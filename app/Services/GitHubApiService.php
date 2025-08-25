<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GitHubApiService
{
    private string $baseUrl = 'https://api.github.com';
    private string $owner;
    private string $repo;
    private ?string $token;

    public function __construct()
    {
        $this->owner = config('app.github_repo_owner', 'omgbwa-yasse');
        $this->repo = config('app.github_repo_name', 'shelve');
        $this->token = config('app.github_token');
    }

    /**
     * Get all repository tags
     */
    public function getRepositoryTags(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withOptions([
                    'verify' => config('app.env') === 'production', // Désactiver SSL en développement
                ])
                ->get("{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/tags");

            if ($response->successful()) {
                $tags = $response->json();

                // Trier les tags par ordre sémantique décroissant
                usort($tags, function ($a, $b) {
                    return version_compare($b['name'], $a['name']);
                });

                return $tags;
            }

            Log::error('Failed to fetch GitHub tags', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [];
        } catch (Exception $e) {
            Log::error('Exception fetching GitHub tags', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get specific tag details
     */
    public function getTagDetails(string $tag): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withOptions([
                    'verify' => config('app.env') === 'production', // Désactiver SSL en développement
                ])
                ->get("{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/releases/tags/{$tag}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('Exception fetching tag details', [
                'tag' => $tag,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get latest release
     */
    public function getLatestRelease(): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withOptions([
                    'verify' => config('app.env') === 'production', // Désactiver SSL en développement
                ])
                ->get("{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/releases/latest");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('Exception fetching latest release', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get all releases
     */
    public function getAllReleases(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withOptions([
                    'verify' => config('app.env') === 'production', // Désactiver SSL en développement
                ])
                ->get("{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/releases");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (Exception $e) {
            Log::error('Exception fetching releases', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Download release archive
     */
    public function downloadRelease(string $tag): ?string
    {
        try {
            $downloadUrl = "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/zipball/{$tag}";

            $response = Http::withHeaders($this->getHeaders())
                ->withOptions([
                    'verify' => config('app.env') === 'production', // Désactiver SSL en développement
                ])
                ->timeout(300) // 5 minutes timeout
                ->get($downloadUrl);

            if ($response->successful()) {
                $tempPath = storage_path("app/updates/{$tag}.zip");

                // Create directory if it doesn't exist
                if (!file_exists(dirname($tempPath))) {
                    mkdir(dirname($tempPath), 0755, true);
                }

                file_put_contents($tempPath, $response->body());

                return $tempPath;
            }

            return null;
        } catch (Exception $e) {
            Log::error('Exception downloading release', [
                'tag' => $tag,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get release notes/changelog
     */
    public function getReleaseNotes(string $tag): ?string
    {
        $release = $this->getTagDetails($tag);
        return $release['body'] ?? null;
    }

    /**
     * Compare versions
     */
    public function compareVersions(string $version1, string $version2): int
    {
        // Remove 'v' prefix if present
        $v1 = ltrim($version1, 'v');
        $v2 = ltrim($version2, 'v');

        return version_compare($v1, $v2);
    }

    /**
     * Check if version is newer than current
     */
    public function isNewerVersion(string $version, string $currentVersion): bool
    {
        return $this->compareVersions($version, $currentVersion) > 0;
    }

    /**
     * Get repository information
     */
    public function getRepositoryInfo(): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withOptions([
                    'verify' => config('app.env') === 'production', // Désactiver SSL en développement
                ])
                ->get("{$this->baseUrl}/repos/{$this->owner}/{$this->repo}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('Exception fetching repository info', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get request headers
     */
    private function getHeaders(): array
    {
        $headers = [
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'Shelve-Update-System'
        ];

        if ($this->token) {
            $headers['Authorization'] = "token {$this->token}";
        }

        return $headers;
    }
}
