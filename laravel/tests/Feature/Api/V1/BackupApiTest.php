<?php

namespace Tests\Feature\Api\V1;

use App\Models\Backup;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D16 — sauvegardes (référentiel global). Portage finalisé le 2026-08-04.
 */
class BackupApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['backup'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeBackup(array $extra = []): Backup
    {
        return Backup::create([
            'date_time' => now(),
            'type' => 'metadata',
            'description' => 'Sauvegarde de test',
            'status' => 'success',
            'user_id' => $this->user->id,
            'size' => 1024,
            'backup_file' => 'backup_test.zip',
            'path' => 'backups/backup_test.zip',
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/backups')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/backups')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makeBackup();
        $this->makeBackup();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/backups')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $backup = $this->makeBackup();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/backups/{$backup->id}")
            ->assertOk()
            ->assertJsonPath('data.type', 'metadata')
            ->assertJsonPath('data.status', 'success')
            ->assertJsonPath('data.user_id', $this->user->id);
    }

    public function test_store_cree_la_ressource_avec_le_user_authentifie(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/backups', [
                'type' => 'full',
                'status' => 'in_progress',
                'description' => 'Nouvelle sauvegarde',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.type', 'full')
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.user_id', $this->user->id);

        $response->assertHeader('Location', "/api/v1/backups/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/backups', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'status']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/backups', ['type' => 'inconnu', 'status' => 'success'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('type');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $backup = $this->makeBackup();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/backups/{$backup->id}", ['status' => 'failed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'failed');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $backup = $this->makeBackup();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/backups/{$backup->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('backups', ['id' => $backup->id]);
    }

    /**
     * Référentiel global : un agent d'une autre organisation lit les mêmes données.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $backup = $this->makeBackup();

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/backups/{$backup->id}")
            ->assertOk();
    }
}
