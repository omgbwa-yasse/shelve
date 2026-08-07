<?php

namespace App\Services\AI\Sandbox\Tools;

use AiBridge\Contracts\ToolContract;
use App\Services\AI\SandboxService;

/**
 * Exécute un script Python dans le workspace du sandbox et retourne la
 * console (stdout + stderr). Le chemin du script est relatif au workspace.
 */
class SandboxRunTool implements ToolContract
{
    use SandboxToolPolicy;

    public function __construct(private SandboxService $sandbox) {}

    protected function requiredPermission(): string
    {
        return 'ai_sandbox_run';
    }

    public function name(): string
    {
        return 'sandbox_run';
    }

    public function description(): string
    {
        return 'Exécute un script Python du sandbox (chemin relatif, ex: core/main.py). '
            . 'Retourne la sortie console. En cas d\'erreur, corrige le code et ré-exécute.';
    }

    public function schema(): array
    {
        return [
            'sandbox_id' => ['type' => 'integer', 'description' => 'Id du sandbox', 'required' => true],
            'script' => ['type' => 'string', 'description' => 'Chemin relatif du script (ex: core/main.py)', 'required' => true],
        ];
    }

    public function execute(array $arguments): string
    {
        $this->authorizeSandbox();

        $sandbox = $this->sandboxOrFail((int) ($arguments['sandbox_id'] ?? 0));

        $result = $this->sandbox->run(
            $sandbox,
            (string) ($arguments['script'] ?? 'core/main.py')
        );

        return json_encode([
            'exit_code' => $result['exit_code'],
            'success' => $result['exit_code'] === 0,
            'output' => $result['output'],
            'error' => $result['error'],
            'status' => $sandbox->status,
            'hint' => $result['exit_code'] !== 0
                ? 'Corrige le code (sandbox_write) puis relance sandbox_run.'
                : null,
        ], JSON_UNESCAPED_UNICODE);
    }
}
