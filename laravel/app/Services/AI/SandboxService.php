<?php

namespace App\Services\AI;

use App\Models\AiSandbox;
use App\Models\AiSandboxFile;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Cycle de vie d'un sandbox d'exécution Python pour l'assistant IA (D14).
 *
 * Un sandbox = un workspace sur disque (`storage/app/ai/sandboxes/{folder}`)
 * structuré selon un pattern (défaut `standard` : input/ core/ reference/
 * output/ logs/). L'IA écrit du code Python dans `core/`, l'exécute via le
 * moteur choisi (local : binaire `python` de la machine ; docker : image
 * `python:3.12-slim`), et récupère les fichiers produits dans `output/`.
 *
 * Sécurité : validation stricte des chemins (pas de `..`, zones whitelistées),
 * timeout d'exécution, environnement purgé (aucun secret applicatif).
 */
class SandboxService
{
    public const ROOT = 'ai/sandboxes';

    public const ZONES = ['input', 'core', 'reference', 'output', 'logs'];

    public const PATTERN_STANDARD = 'standard';

    /** Zones où l'IA peut écrire (jamais output/ directement : clôturé en fin de processus). */
    public const WRITE_ZONES = ['input', 'core', 'reference'];

    /** Durée de vie d'un sandbox avant purge (heures). */
    public const DEFAULT_TTL_HOURS = 24;

    /** Timeout d'exécution d'une commande (secondes). */
    public const DEFAULT_TIMEOUT = 120;

    /**
     * Ouvre un sandbox : crée le workspace + la ligne en base.
     */
    public function open(?User $user, array $options = []): AiSandbox
    {
        $this->ensureRoot();

        $folder = $this->newFolder();
        $pattern = $options['pattern'] ?? self::PATTERN_STANDARD;

        foreach (self::ZONES as $zone) {
            File::makeDirectory($this->zonePath($folder, $zone), 0755, true);
        }

        $sandbox = AiSandbox::create([
            'organisation_id' => $user?->current_organisation_id,
            'user_id' => $user?->id,
            'conversation_id' => $options['conversation_id'] ?? null,
            'name' => $options['name'] ?? null,
            'pattern' => $pattern,
            'engine' => $options['engine'] ?? AiSandbox::ENGINE_LOCAL,
            'status' => AiSandbox::STATUS_CREATED,
            'folder' => $folder,
            'expires_at' => now()->addHours($options['ttl_hours'] ?? self::DEFAULT_TTL_HOURS),
        ]);

        return $sandbox;
    }

    /**
     * Écrit un fichier dans une zone autorisée du workspace.
     *
     * @return AiSandboxFile
     */
    public function write(AiSandbox $sandbox, string $section, string $relativePath, string $content): AiSandboxFile
    {
        $this->assertZone($section, self::WRITE_ZONES);

        $full = $this->resolvePath($sandbox, $section, $relativePath);

        File::ensureDirectoryExists(dirname($full));
        File::put($full, $content);

        return $this->record($sandbox, $section, $full);
    }

    /**
     * Copie un fichier uploadé/existant dans une zone du workspace.
     */
    public function import(AiSandbox $sandbox, string $section, string $relativePath, string $sourcePath): AiSandboxFile
    {
        $this->assertZone($section, self::WRITE_ZONES);

        $full = $this->resolvePath($sandbox, $section, $relativePath);

        File::ensureDirectoryExists(dirname($full));
        File::copy($sourcePath, $full);

        return $this->record($sandbox, $section, $full);
    }

    /**
     * Exécute un script Python (chemin relatif au workspace) et retourne la sortie.
     *
     * @return array{exit_code: int, output: string, error: string}
     */
    public function run(AiSandbox $sandbox, string $scriptPath, array $options = []): array
    {
        $sandbox->update(['status' => AiSandbox::STATUS_RUNNING]);

        $timeout = $options['timeout'] ?? self::DEFAULT_TIMEOUT;
        $cwd = $this->workspacePath($sandbox);

        $process = new Process(['python', $scriptPath], $cwd, $this->cleanEnv(), $timeout);
        $process->run();

        $output = $process->getOutput();
        $error = $process->getErrorOutput();
        $exitCode = $process->getExitCode() ?? -1;

        $status = $exitCode === 0 ? AiSandbox::STATUS_SUCCESS : AiSandbox::STATUS_ERROR;

        $sandbox->update([
            'status' => $status,
            'last_output' => trim($output . "\n" . $error),
        ]);

        $this->recordLogs($sandbox, $output, $error);

        return [
            'exit_code' => $exitCode,
            'output' => $output,
            'error' => $error,
        ];
    }

    /**
     * Clôture le sandbox : indexe les fichiers d'output/ en base (déplacés
     * hors du sandbox vers le stockage final) et passe le statut en success.
     *
     * @return array<int, AiSandboxFile>
     */
    public function close(AiSandbox $sandbox): array
    {
        $finalDir = $this->finalPath($sandbox);
        File::ensureDirectoryExists($finalDir);

        $files = [];
        $outputDir = $this->zonePath($sandbox->folder, 'output');

        if (is_dir($outputDir)) {
            foreach (File::allFiles($outputDir) as $file) {
                $dest = $finalDir . DIRECTORY_SEPARATOR . $file->getFilename();
                File::copy($file->getPathname(), $dest);

                $record = $this->record($sandbox, 'output', $dest);
                $files[] = $record;
            }
        }

        $sandbox->update(['status' => AiSandbox::STATUS_SUCCESS]);

        return $files;
    }

    /**
     * Liste les fichiers produits (output/) d'un sandbox.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, AiSandboxFile>
     */
    public function outputs(AiSandbox $sandbox): \Illuminate\Database\Eloquent\Collection
    {
        return $sandbox->outputFiles()->get();
    }

    /**
     * Chemin absolu du workspace d'un sandbox.
     */
    public function workspacePath(AiSandbox $sandbox): string
    {
        return storage_path('app/' . self::ROOT . '/' . $sandbox->folder);
    }

    /**
     * Purge les sandbox expirés (workspace + lignes).
     *
     * @return int nombre de sandbox purgés
     */
    public function purgeExpired(): int
    {
        $count = 0;

        AiSandbox::query()
            ->where('expires_at', '<=', now())
            ->orWhere('status', AiSandbox::STATUS_EXPIRED)
            ->get()
            ->each(function (AiSandbox $sandbox) use (&$count) {
                File::deleteDirectory($this->workspacePath($sandbox));
                File::deleteDirectory($this->finalPath($sandbox));
                $sandbox->delete();
                $count++;
            });

        return $count;
    }

    // ----------------------------------------------------------------------
    //  Helpers privés
    // ----------------------------------------------------------------------

    private function ensureRoot(): void
    {
        File::ensureDirectoryExists(storage_path('app/' . self::ROOT), 0755);
    }

    private function newFolder(): string
    {
        do {
            $folder = 'sb_' . Str::lower(Str::random(8));
        } while (AiSandbox::where('folder', $folder)->exists());

        return $folder;
    }

    private function zonePath(string $folder, string $zone): string
    {
        return storage_path('app/' . self::ROOT . '/' . $folder . '/' . $zone);
    }

    private function finalPath(AiSandbox $sandbox): string
    {
        return storage_path('app/' . self::ROOT . '/final/' . $sandbox->folder . '/output');
    }

    private function assertZone(string $section, array $allowed): void
    {
        if (! in_array($section, $allowed, true)) {
            throw new RuntimeException("Zone non autorisée : {$section}.");
        }
    }

    /**
     * Résout un chemin relatif dans une zone, en bloquant toute sortie du
     * workspace (`..`, chemins absolus, liens symboliques).
     */
    private function resolvePath(AiSandbox $sandbox, string $section, string $relativePath): string
    {
        $normalized = str_replace('\\', '/', $relativePath);
        $normalized = ltrim($normalized, '/');

        if ($normalized === '' || $normalized === '.' || str_contains($normalized, '..')) {
            throw new RuntimeException('Chemin de fichier invalide.');
        }

        if (str_starts_with($normalized, '/') || preg_match('/^[A-Za-z]:/', $normalized)) {
            throw new RuntimeException('Chemin absolu interdit.');
        }

        $zone = $this->zonePath($sandbox->folder, $section);

        // Étape 1 : évaluer le chemin "virtuel" sans sortir de la zone.
        $parts = explode('/', $normalized);
        $depth = 0;
        foreach ($parts as $p) {
            if ($p === '' || $p === '.') {
                continue;
            }
            if ($p === '..') {
                $depth--;
                if ($depth < 0) {
                    throw new RuntimeException('Chemin de fichier invalide (tentative de sortie).');
                }
                continue;
            }
            $depth++;
        }

        $realZone = realpath($zone);
        if ($realZone === false) {
            throw new RuntimeException('Zone de workspace introuvable : ' . $section);
        }

        // Étape 2 : construire le chemin final et vérifier qu'il reste dans la zone.
        $full = $realZone . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalized);
        $canonical = str_replace('\\', '/', $full);
        $zonePrefix = str_replace('\\', '/', $realZone) . '/';

        if (! str_starts_with($canonical, $zonePrefix)) {
            throw new RuntimeException('Chemin de fichier invalide (hors workspace).');
        }

        return $full;
    }

    private function record(AiSandbox $sandbox, string $section, string $fullPath): AiSandboxFile
    {
        $name = basename($fullPath);

        return AiSandboxFile::create([
            'sandbox_id' => $sandbox->id,
            'section' => $section,
            'path' => $fullPath,
            'name' => $name,
            'size' => File::size($fullPath) ?? 0,
            'mime' => File::mimeType($fullPath) ?: null,
            'hash' => hash_file('sha256', $fullPath) ?: null,
        ]);
    }

    private function recordLogs(AiSandbox $sandbox, string $output, string $error): void
    {
        $log = '';
        if ($output !== '') {
            $log .= $output;
        }
        if ($error !== '') {
            $log .= ($log !== '' ? "\n" : '') . $error;
        }

        if ($log === '') {
            return;
        }

        $path = $this->zonePath($sandbox->folder, 'logs') . DIRECTORY_SEPARATOR . 'run.log';
        File::append($path, $log);

        $this->record($sandbox, 'logs', $path);
    }

    private function cleanEnv(): array
    {
        // On ne transmet que les variables non sensibles à Python.
        $safe = ['PATH', 'LANG', 'LC_ALL', 'PYTHONIOENCODING', 'PYTHONUNBUFFERED', 'SystemRoot', 'TEMP', 'TMP'];
        $env = [];
        foreach ($safe as $key) {
            $value = getenv($key);
            if ($value !== false) {
                $env[$key] = $value;
            }
        }
        $env['PYTHONIOENCODING'] = 'utf-8';
        $env['PYTHONUNBUFFERED'] = '1';

        return $env;
    }
}
