<?php

namespace App\Services\AI\Sandbox\Tools;

use AiBridge\Contracts\ToolContract;
use App\Services\AI\SandboxService;

/**
 * Clôture le sandbox : indexe les fichiers produits dans output/ et retourne
 * la liste des artefacts (noms + chemins). À appeler une fois le travail fini.
 */
class SandboxCloseTool implements ToolContract
{
    use SandboxToolPolicy;

    public function __construct(private SandboxService $sandbox) {}

    protected function requiredPermission(): string
    {
        return 'ai_sandbox_close';
    }

    public function name(): string
    {
        return 'sandbox_close';
    }

    public function description(): string
    {
        return 'Clôture le sandbox et récupère les fichiers produits (output/). '
            . 'À appeler quand le travail est terminé pour obtenir la liste des artefacts.';
    }

    public function schema(): array
    {
        return [
            'sandbox_id' => ['type' => 'integer', 'description' => 'Id du sandbox', 'required' => true],
            'summary' => ['type' => 'string', 'description' => 'Résumé du travail réalisé (pour l\'historique)', 'required' => false],
        ];
    }

    public function execute(array $arguments): string
    {
        $this->authorizeSandbox();

        $sandbox = $this->sandboxOrFail((int) ($arguments['sandbox_id'] ?? 0));

        $files = $this->sandbox->close($sandbox);

        $artefacts = array_map(fn ($f) => [
            'id' => $f->id,
            'name' => $f->name,
            'size' => $f->size,
            'mime' => $f->mime,
            'path' => $f->path,
        ], $files);

        return json_encode([
            'closed' => true,
            'status' => $sandbox->status,
            'sandbox_id' => $sandbox->id,
            'files_count' => count($artefacts),
            'files' => $artefacts,
        ], JSON_UNESCAPED_UNICODE);
    }
}
