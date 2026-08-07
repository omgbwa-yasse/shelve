<?php

namespace App\Services\AI;

use App\Models\AiSkill;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class AiSkillService
{
    public const SYSTEM_DIR = 'ai/skills/system';
    public const CUSTOM_DIR = 'ai/skills/custom';

    public function ensureDirectories(): void
    {
        foreach ([self::SYSTEM_DIR, self::CUSTOM_DIR] as $dir) {
            $path = storage_path('app/' . $dir);
            if (!is_dir($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }
    }

    public function systemPath(): string
    {
        return storage_path('app/' . self::SYSTEM_DIR);
    }

    public function customPath(): string
    {
        return storage_path('app/' . self::CUSTOM_DIR);
    }

    /**
     * Installe un skill depuis un ZIP uploadé.
     */
    public function installFromZip(UploadedFile $zip): AiSkill
    {
        $this->ensureDirectories();

        if ($zip->getClientOriginalExtension() !== 'zip' && $zip->getMimeType() !== 'application/zip') {
            throw new RuntimeException('Le fichier doit être une archive ZIP.');
        }

        $tmpDir = storage_path('app/ai/skills/.tmp-' . Str::random(8));
        File::makeDirectory($tmpDir, 0755, true);

        try {
            $tmpZip = $tmpDir . '/skill.zip';
            $zip->move($tmpDir, 'skill.zip');

            $archive = new ZipArchive();
            if ($archive->open($tmpZip) !== true) {
                throw new RuntimeException('Archive ZIP illisible.');
            }

            // Déterminer le dossier racine du skill dans l'archive
            $root = $this->detectRootInArchive($archive);
            $folder = $root !== '' ? basename($root) : $zip->getClientOriginalName();

            // Extraire vers le dossier custom final
            $dest = $this->customPath() . DIRECTORY_SEPARATOR . Str::slug($folder);
            if (is_dir($dest)) {
                File::deleteDirectory($dest);
            }
            if (!is_dir($dest)) {
                File::makeDirectory($dest, 0755, true);
            }

            $archive->extractTo($dest);
            $archive->close();

            // Si le skill était dans un sous-dossier, remonter les fichiers
            if ($root !== '') {
                $rootPath = $dest . DIRECTORY_SEPARATOR . $root;
                if (is_dir($rootPath)) {
                    $this->flattenRoot($rootPath, $dest);
                }
            }

            $skillMd = $dest . '/SKILL.md';
            if (!file_exists($skillMd)) {
                File::deleteDirectory($dest);
                throw new RuntimeException("Skill invalide : le fichier SKILL.md est absent à la racine du ZIP.");
            }

            $meta = $this->parseSkillMd($skillMd);
            $slug = $meta['slug'] ?? Str::slug($folder);

            $skill = AiSkill::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $meta['name'] ?? Str::title(str_replace(['-', '_'], ' ', $slug)),
                    'description' => $meta['description'] ?? null,
                    'version' => $meta['version'] ?? null,
                    'location' => 'custom',
                    'folder' => basename($dest),
                    'enabled' => true,
                    'installed_by' => auth()->id(),
                ]
            );

            return $skill;
        } finally {
            File::deleteDirectory($tmpDir);
        }
    }

    public function delete(AiSkill $skill): void
    {
        if ($skill->location === 'system') {
            throw new RuntimeException('Un skill système ne peut pas être supprimé.');
        }
        File::deleteDirectory($skill->base_path);
        $skill->delete();
    }

    public function systemSkills(): array
    {
        return $this->scanDirectory($this->systemPath(), 'system');
    }

    public function customSkills(): array
    {
        return $this->scanDirectory($this->customPath(), 'custom');
    }

    /**
     * Parcourt un répertoire de skills et retourne les dossiers contenant un SKILL.md.
     */
    private function scanDirectory(string $dir, string $location): array
    {
        $skills = [];
        if (!is_dir($dir)) {
            return $skills;
        }

        foreach (File::directories($dir) as $folderPath) {
            $folder = basename($folderPath);
            $skillMd = $folderPath . '/SKILL.md';
            if (!file_exists($skillMd)) {
                continue;
            }

            $meta = $this->parseSkillMd($skillMd);
            $slug = $meta['slug'] ?? Str::slug($folder);

            $record = AiSkill::firstOrCreate(
                ['slug' => $slug, 'location' => $location],
                [
                    'name' => $meta['name'] ?? Str::title(str_replace(['-', '_'], ' ', $slug)),
                    'description' => $meta['description'] ?? null,
                    'version' => $meta['version'] ?? null,
                    'folder' => $folder,
                    'enabled' => true,
                ]
            );

            $skills[] = [
                'record' => $record,
                'folder' => $folder,
                'path' => $folderPath,
                'skill_md' => $skillMd,
                'meta' => $meta,
                'resources' => $this->listResources($folderPath),
            ];
        }

        return collect($skills)->sortByDesc(fn ($s) => $s['record']->location === 'system')->values()->all();
    }

    private function listResources(string $folderPath): array
    {
        $items = [];
        foreach (File::allFiles($folderPath) as $file) {
            if ($file->getFilename() === 'SKILL.md') {
                continue;
            }
            $items[] = [
                'name' => $file->getFilename(),
                'path' => str_replace('\\', '/', $file->getPathname()),
                'size' => $file->getSize(),
                'relative' => str_replace('\\', '/', substr($file->getPathname(), strlen($folderPath) + 1)),
            ];
        }
        return $items;
    }

    public function parseSkillMd(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $meta = [];

        // Frontmatter YAML simple (--- ... ---)
        if (preg_match('/^---\s*\n(.*?)\n---/s', $content, $m)) {
            foreach (explode("\n", $m[1]) as $line) {
                if (preg_match('/^\s*([\w-]+)\s*:\s*(.*)$/', $line, $kv)) {
                    $meta[strtolower($kv[1])] = trim($kv[2], " \t\"'");
                }
            }
        }

        $meta['name'] = $meta['name'] ?? (preg_match('/^#\s+(.+)$/m', $content, $hm) ? trim($hm[1]) : null);
        $meta['description'] = $meta['description'] ?? null;
        $meta['version'] = $meta['version'] ?? null;
        $meta['slug'] = $meta['slug'] ?? null;

        return $meta;
    }

    private function detectRootInArchive(ZipArchive $archive): string
    {
        $files = [];
        for ($i = 0; $i < $archive->numFiles; $i++) {
            $files[] = $archive->getNameIndex($i);
        }

        // Si un SKILL.md est à la racine, pas de sous-dossier
        if (in_array('SKILL.md', $files)) {
            return '';
        }

        // Trouver le dossier contenant SKILL.md
        foreach ($files as $file) {
            if (str_ends_with($file, '/SKILL.md')) {
                return dirname($file);
            }
        }

        return '';
    }

    private function flattenRoot(string $rootPath, string $dest): void
    {
        foreach (File::allFiles($rootPath) as $file) {
            $relative = substr($file->getPathname(), strlen($rootPath) + 1);
            $target = $dest . DIRECTORY_SEPARATOR . $relative;
            $targetDir = dirname($target);
            if (!is_dir($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            File::copy($file->getPathname(), $target);
        }
        File::deleteDirectory($rootPath);
    }
}
