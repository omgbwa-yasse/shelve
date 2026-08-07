<?php

namespace App\Services\AI\Sandbox\Tools;

use AiBridge\Contracts\ToolContract;
use App\Services\AI\SandboxService;

/**
 * Écrit un fichier dans le workspace du sandbox (zones autorisées :
 * input/ core/ reference/). Le chemin est validé (pas de sortie de zone).
 */
class SandboxWriteTool implements ToolContract
{
    use SandboxToolPolicy;

    public function __construct(private SandboxService $sandbox) {}

    protected function requiredPermission(): string
    {
        return 'ai_sandbox_write';
    }

    public function name(): string
    {
        return 'sandbox_write';
    }

    public function description(): string
    {
        return 'Écrit un fichier dans le sandbox. Zones autorisées : core (code Python), '
            . 'input (données), reference. Ne jamais écrire dans output (réservé à la clôture).';
    }

    public function schema(): array
    {
        return [
            'sandbox_id' => ['type' => 'integer', 'description' => 'Id du sandbox', 'required' => true],
            'section' => ['type' => 'string', 'description' => 'core | input | reference', 'required' => true],
            'path' => ['type' => 'string', 'description' => 'Chemin relatif dans la zone (ex: main.py, data.csv, sub/module.py)', 'required' => true],
            'content' => ['type' => 'string', 'description' => 'Contenu du fichier', 'required' => true],
        ];
    }

    public function execute(array $arguments): string
    {
        $this->authorizeSandbox();

        $sandbox = $this->sandboxOrFail((int) ($arguments['sandbox_id'] ?? 0));

        $file = $this->sandbox->write(
            $sandbox,
            (string) ($arguments['section'] ?? 'core'),
            (string) ($arguments['path'] ?? ''),
            (string) ($arguments['content'] ?? '')
        );

        return json_encode([
            'written' => true,
            'section' => $file->section,
            'path' => $file->path,
            'name' => $file->name,
            'size' => $file->size,
        ], JSON_UNESCAPED_UNICODE);
    }
}
