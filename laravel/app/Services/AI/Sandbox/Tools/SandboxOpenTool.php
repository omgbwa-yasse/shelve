<?php

namespace App\Services\AI\Sandbox\Tools;

use AiBridge\Contracts\ToolContract;
use App\Services\AI\SandboxService;
use Illuminate\Support\Facades\Auth;

/**
 * Ouvre un sandbox Python (workspace standard + ligne ai_sandboxes).
 *
 * Argument : `pattern` (standard), `conversation_id` (facultatif),
 * `name` (facultatif), `purpose` (facultatif, explication pour l'historique).
 */
class SandboxOpenTool implements ToolContract
{
    use SandboxToolPolicy;

    public function __construct(private SandboxService $sandbox) {}

    protected function requiredPermission(): string
    {
        return 'ai_sandbox_open';
    }

    public function name(): string
    {
        return 'sandbox_open';
    }

    public function description(): string
    {
        return 'Ouvre un sandbox Python pour générer des fichiers (PDF, graphiques, Excel, analyses). '
            . 'À utiliser avant sandbox_write / sandbox_run.';
    }

    public function schema(): array
    {
        return [
            'pattern' => ['type' => 'string', 'description' => 'Pattern de workspace. Valeur : standard', 'required' => false],
            'conversation_id' => ['type' => 'integer', 'description' => 'Id de la conversation AI en cours', 'required' => false],
            'name' => ['type' => 'string', 'description' => 'Libellé du sandbox (ex: Régression CA)', 'required' => false],
            'purpose' => ['type' => 'string', 'description' => 'But de la tâche, pour traçabilité', 'required' => false],
        ];
    }

    public function execute(array $arguments): string
    {
        $this->authorizeSandbox();

        $user = Auth::user();

        $sandbox = $this->sandbox->open($user, [
            'pattern' => $arguments['pattern'] ?? 'standard',
            'conversation_id' => $arguments['conversation_id'] ?? null,
            'name' => $arguments['name'] ?? null,
            'engine' => 'local',
        ]);

        return json_encode([
            'sandbox_id' => $sandbox->id,
            'folder' => $sandbox->folder,
            'pattern' => $sandbox->pattern,
            'workspace' => [
                'input', 'core', 'reference', 'output', 'logs',
            ],
            'instructions' => "Écris ton code Python dans core/, tes données dans input/, "
                . "les fichiers produits (PDF, PNG, XLSX...) seront récupérés depuis output/ à la clôture.",
        ], JSON_UNESCAPED_UNICODE);
    }
}
