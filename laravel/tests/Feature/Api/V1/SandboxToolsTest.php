<?php

namespace Tests\Feature\Api\V1;

use AiBridge\Contracts\ToolContract;
use App\Models\Organisation;
use App\Models\Permission;
use App\Models\User;
use App\Services\AI\Sandbox\Tools\SandboxCloseTool;
use App\Services\AI\Sandbox\Tools\SandboxOpenTool;
use App\Services\AI\Sandbox\Tools\SandboxRunTool;
use App\Services\AI\Sandbox\Tools\SandboxWriteTool;
use App\Services\AI\SandboxService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * D14 — outils sandbox exposés à l'IA (ToolContract) : open → write → run → close,
 * avec contrôle des permissions `ai_sandbox_*` et isolation par organisation (R03).
 */
class SandboxToolsTest extends TestCase
{
    use DatabaseTransactions;

    private const SANDBOX_PERMISSIONS = [
        'ai_sandbox_open', 'ai_sandbox_write', 'ai_sandbox_run', 'ai_sandbox_close',
    ];

    private SandboxService $sandbox;

    private User $user;

    private SandboxOpenTool $open;

    private SandboxWriteTool $write;

    private SandboxRunTool $run;

    private SandboxCloseTool $close;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandbox = app(SandboxService::class);
        $this->user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->grantSandboxPermissions($this->user);

        $this->actingAs($this->user);

        $this->open = new SandboxOpenTool($this->sandbox);
        $this->write = new SandboxWriteTool($this->sandbox);
        $this->run = new SandboxRunTool($this->sandbox);
        $this->close = new SandboxCloseTool($this->sandbox);
    }

    private function grantSandboxPermissions(User $user): void
    {
        $ids = collect(self::SANDBOX_PERMISSIONS)->map(fn (string $name) => Permission::firstOrCreate(
            ['name' => $name],
            ['category' => 'ai', 'description' => $name . ' (test)', 'guard_name' => 'web']
        )->id)->all();

        $user->permissions()->syncWithoutDetaching($ids);
    }

    private function userWithoutPermissions(): User
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        return $user;
    }

    public function test_tools_implementent_tool_contract(): void
    {
        $this->assertInstanceOf(ToolContract::class, $this->open);
        $this->assertInstanceOf(ToolContract::class, $this->write);
        $this->assertInstanceOf(ToolContract::class, $this->run);
        $this->assertInstanceOf(ToolContract::class, $this->close);
    }

    public function test_flux_complet_open_write_run_close(): void
    {
        // open
        $res = json_decode($this->open->execute(['name' => 'Test outils']), true);
        $this->assertArrayHasKey('sandbox_id', $res);
        $sandboxId = $res['sandbox_id'];

        // write
        $w = json_decode($this->write->execute([
            'sandbox_id' => $sandboxId,
            'section' => 'core',
            'path' => 'main.py',
            'content' => 'open("output/resultat.txt", "w").write("fait")',
        ]), true);
        $this->assertTrue($w['written']);

        // run
        $r = json_decode($this->run->execute(['sandbox_id' => $sandboxId, 'script' => 'core/main.py']), true);
        $this->assertSame(0, $r['exit_code']);
        $this->assertTrue($r['success']);

        // close
        $c = json_decode($this->close->execute(['sandbox_id' => $sandboxId]), true);
        $this->assertTrue($c['closed']);
        $this->assertSame(1, $c['files_count']);
        $this->assertSame('resultat.txt', $c['files'][0]['name']);
    }

    public function test_write_et_run_rejettent_un_sandbox_dun_autre_user(): void
    {
        $other = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $sb = $this->sandbox->open($other);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sandbox introuvable ou non autorisé');

        $this->write->execute(['sandbox_id' => $sb->id, 'section' => 'core', 'path' => 'x.py', 'content' => '']);
    }

    public function test_run_retourne_lerreur_et_status_error(): void
    {
        $sb = $this->sandbox->open($this->user);
        $this->write->execute(['sandbox_id' => $sb->id, 'section' => 'core', 'path' => 'boom.py', 'content' => 'raise ValueError("paf")']);

        $r = json_decode($this->run->execute(['sandbox_id' => $sb->id, 'script' => 'core/boom.py']), true);

        $this->assertFalse($r['success']);
        $this->assertStringContainsString('paf', $r['error']);
        $this->assertSame('error', $r['status']);
    }

    public function test_open_refuse_sans_permission(): void
    {
        $this->actingAs($this->userWithoutPermissions());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ai_sandbox_open');

        $this->open->execute([]);
    }

    public function test_write_refuse_sans_permission(): void
    {
        $sb = $this->sandbox->open($this->user);

        $this->actingAs($this->userWithoutPermissions());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ai_sandbox_write');

        $this->write->execute(['sandbox_id' => $sb->id, 'section' => 'core', 'path' => 'x.py', 'content' => '']);
    }

    public function test_changement_dorg_bloque_lacces_residuel(): void
    {
        $orgA = Organisation::factory()->create();
        $orgB = Organisation::factory()->create();
        $user = User::factory()->forOrganisation($orgA)->create();
        $this->grantSandboxPermissions($user);

        $this->actingAs($user);
        $open = new SandboxOpenTool($this->sandbox);
        $write = new SandboxWriteTool($this->sandbox);

        $res = json_decode($open->execute([]), true);
        $sbId = $res['sandbox_id'];

        // L'agent bascule sur une autre organisation.
        $user->update(['current_organisation_id' => $orgB->id]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('organisation');

        $write->execute(['sandbox_id' => $sbId, 'section' => 'core', 'path' => 'x.py', 'content' => '']);
    }
}
