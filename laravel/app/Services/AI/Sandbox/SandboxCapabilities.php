<?php

namespace App\Services\AI\Sandbox;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Charge le catalogue des capacités du sandbox Python (recettes par tâche)
 * depuis `storage/app/ai/sandbox/capabilities.json`.
 *
 * Le catalogue est injecté dans le prompt système du chat pour que l'IA
 * connaisse précisément ce qu'elle peut faire dans le sandbox.
 */
class SandboxCapabilities
{
    public const PATH = 'ai/sandbox/capabilities.json';

    /**
     * Retourne le manifeste complet (tableau associatif).
     *
     * @return array<string, mixed>
     */
    public function manifest(): array
    {
        $disk = Storage::disk('local');
        if (! $disk->exists(self::PATH)) {
            return ['runtime' => 'python3.12', 'patterns' => ['standard'], 'capabilities' => []];
        }

        $decoded = json_decode($disk->get(self::PATH), true);

        return is_array($decoded) ? $decoded : ['runtime' => 'python3.12', 'patterns' => ['standard'], 'capabilities' => []];
    }

    /**
     * Texte du catalogue à injecter dans le prompt système.
     */
    public function toPrompt(): string
    {
        $manifest = $this->manifest();

        $lines = [];
        $lines[] = "SANDBOX PYTHON - CAPACITÉS DISPONIBLES";
        $lines[] = "Runtime : {$manifest['runtime']}. Tu peux écrire du code Python dans le sandbox pour produire "
            . 'des fichiers (PDF, PNG, XLSX, CSV) ou des analyses.';
        $lines[] = '';
        $lines[] = 'Voici les tâches que tu sais faire (id, bibliothèques, entrée/sortie attendues et exemple de code). '
            . 'Choisis une capacité ou compose-en plusieurs :';

        foreach ($manifest['capabilities'] as $cap) {
            $lines[] = '';
            $lines[] = "### {$cap['title']} (id: {$cap['id']})";
            $lines[] = 'Entrée : ' . ($cap['input'] ?? '—');
            $lines[] = 'Bibliothèques : ' . implode(', ', $cap['libraries'] ?? []);
            $lines[] = 'Sortie : ' . ($cap['output'] ?? '—');
            $lines[] = "Exemple de code :\n```python\n{$cap['example']}\n```";
        }

        $lines[] = '';
        $lines[] = 'FLUX D\'UTILISATION : 1) sandbox_open 2) sandbox_write (code dans core/, données dans input/) '
            . '3) sandbox_run 4) sandbox_close pour récupérer les fichiers. ';

        return implode("\n", $lines);
    }
}
