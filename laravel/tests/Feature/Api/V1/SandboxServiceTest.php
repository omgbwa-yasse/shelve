<?php

namespace Tests\Feature\Api\V1;

use App\Models\AiSandbox;
use App\Models\AiSandboxFile;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AI\SandboxService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;
use Tests\TestCase;

/**
 * D14 — cycle de vie d'un sandbox Python (open → write → run → close) et
 * validation de la sécurité des chemins.
 */
class SandboxServiceTest extends TestCase
{
    use DatabaseTransactions;

    private SandboxService $sandbox;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandbox = app(SandboxService::class);
        $this->user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
    }

    public function test_open_cree_un_workspace_standard(): void
    {
        $sb = $this->sandbox->open($this->user, ['name' => 'Test sandbox']);

        $this->assertSame(AiSandbox::STATUS_CREATED, $sb->status);
        $this->assertSame('standard', $sb->pattern);
        $this->assertSame('local', $sb->engine);
        $this->assertNotNull($sb->expires_at);

        $workspace = $this->sandbox->workspacePath($sb);
        foreach (['input', 'core', 'reference', 'output', 'logs'] as $zone) {
            $this->assertDirectoryExists($workspace . DIRECTORY_SEPARATOR . $zone, "zone {$zone} absente");
        }

        $sb->delete();
    }

    public function test_write_cree_le_fichier_et_lenregistre(): void
    {
        $sb = $this->sandbox->open($this->user);

        $file = $this->sandbox->write($sb, 'core', 'main.py', 'print("ok")');

        $this->assertSame('main.py', $file->name);
        $this->assertSame('core', $file->section);
        $this->assertFileExists($file->path);
        $this->assertDatabaseHas('ai_sandbox_files', ['sandbox_id' => $sb->id, 'name' => 'main.py']);

        $sb->delete();
    }

    public function test_write_rejette_la_sortie_de_zone(): void
    {
        $sb = $this->sandbox->open($this->user);

        try {
            $this->sandbox->write($sb, 'core', '../../etc/evil.txt', 'x');
            $this->fail('La sortie de zone aurait dû être bloquée.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('invalide', $e->getMessage());
        } finally {
            $sb->delete();
        }
    }

    public function test_write_rejette_le_chemin_absolu(): void
    {
        $sb = $this->sandbox->open($this->user);

        try {
            $this->sandbox->write($sb, 'core', 'C:/Windows/evil.txt', 'x');
            $this->fail('Le chemin absolu aurait dû être bloqué.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('absolu', $e->getMessage());
        } finally {
            $sb->delete();
        }
    }

    public function test_write_rejette_la_zone_output(): void
    {
        $sb = $this->sandbox->open($this->user);

        try {
            $this->sandbox->write($sb, 'output', 'x.txt', 'x');
            $this->fail('La zone output aurait dû être interdite en écriture directe.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Zone non autorisée', $e->getMessage());
        } finally {
            $sb->delete();
        }
    }

    public function test_write_accepte_les_sous_dossiers(): void
    {
        $sb = $this->sandbox->open($this->user);

        $file = $this->sandbox->write($sb, 'core', 'sub/module.py', 'print(1)');

        $this->assertSame('module.py', $file->name);
        $this->assertFileExists($file->path);

        $sb->delete();
    }

    public function test_run_execute_le_script_python(): void
    {
        $sb = $this->sandbox->open($this->user);
        $this->sandbox->write($sb, 'core', 'hello.py', 'print("hello-sandbox")');

        $result = $this->sandbox->run($sb, 'core/hello.py');

        $this->assertSame(0, $result['exit_code']);
        $this->assertStringContainsString('hello-sandbox', $result['output']);
        $this->assertSame(AiSandbox::STATUS_SUCCESS, $sb->fresh()->status);

        $sb->delete();
    }

    public function test_run_capture_les_erreurs(): void
    {
        $sb = $this->sandbox->open($this->user);
        $this->sandbox->write($sb, 'core', 'boom.py', 'raise ValueError("kaboom")');

        $result = $this->sandbox->run($sb, 'core/boom.py');

        $this->assertNotSame(0, $result['exit_code']);
        $this->assertStringContainsString('kaboom', $result['error']);
        $this->assertSame(AiSandbox::STATUS_ERROR, $sb->fresh()->status);

        $sb->delete();
    }

    public function test_close_indexe_les_fichiers_output(): void
    {
        $sb = $this->sandbox->open($this->user);
        $this->sandbox->write($sb, 'core', 'gen.py', 'open("output/resultat.txt", "w").write("done")');

        $this->sandbox->run($sb, 'core/gen.py');
        $files = $this->sandbox->close($sb);

        $this->assertCount(1, $files);
        $this->assertSame('resultat.txt', $files[0]->name);
        $this->assertSame('output', $files[0]->section);
        $this->assertSame(AiSandbox::STATUS_SUCCESS, $sb->fresh()->status);

        $sb->delete();
    }
}
