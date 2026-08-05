<?php

namespace Tests\Feature\Api\V1;

use App\Models\Attachment;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordAttachment;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D02 — pièces jointes d'une notice (pivot `record_physical_attachment`, org-scopée
 * par la notice parente, motif D03). Portage finalisé le 2026-08-05 : ressource
 * imbriquée sous `/records/{record}/attachments`, la création passe par l'upload.
 */
class RecordAttachmentApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['record_attachment'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeRecord(Organisation $org = null): Record
    {
        $org = $org ?? $this->organisation;
        $level = RecordLevel::first() ?? RecordLevel::create(['name' => 'Niveau test']);
        $status = RecordStatus::first() ?? RecordStatus::create(['name' => 'Brouillon']);

        return Record::create([
            'code' => 'R' . substr(uniqid(), -8),
            'name' => 'Notice test',
            'level_id' => $level->id,
            'status_id' => $status->id,
            'access_level' => 'internal',
            'organisation_id' => $org->id,
            'creator_id' => $this->user->id,
            'version_number' => 1,
            'is_current_version' => true,
        ]);
    }

    private function makeAttachment(): Attachment
    {
        return Attachment::create([
            'path' => 'attachments/test-' . substr(uniqid(), -8) . '.pdf',
            'name' => 'piece-jointe.pdf',
            'crypt' => 'md5',
            'crypt_sha512' => 'sha512',
            'size' => 1024,
            'creator_id' => $this->user->id,
            'type' => 'record',
            'mime_type' => 'application/pdf',
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/records/1/attachments')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $record = $this->makeRecord();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/attachments")
            ->assertStatus(403);
    }

    public function test_index_retourne_les_pieces_jointes_de_la_notice(): void
    {
        $record = $this->makeRecord();
        $attachment = $this->makeAttachment();
        $record->attachments()->attach($attachment->id);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/attachments")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attachment_id', $attachment->id);
    }

    public function test_upload_cree_la_piece_jointe_et_la_pivot(): void
    {
        Storage::fake('local');

        $record = $this->makeRecord();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/attachments/upload", [
                'file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
                'name' => 'Document uploadé',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.record_id', $record->id);

        $attachmentId = $response->json('data.attachment_id');
        $this->assertNotNull($attachmentId);
        $this->assertDatabaseHas('attachments', ['id' => $attachmentId, 'type' => 'record']);
        $this->assertDatabaseHas('record_physical_attachment', [
            'record_id' => $record->id,
            'attachment_id' => $attachmentId,
        ]);
    }

    public function test_upload_valide_ses_entrees(): void
    {
        $record = $this->makeRecord();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/attachments/upload", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_destroy_supprime_la_piece_jointe(): void
    {
        Storage::fake('local');

        $record = $this->makeRecord();
        $attachment = $this->makeAttachment();
        $record->attachments()->attach($attachment->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}/attachments/{$attachment->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('record_physical_attachment', [
            'record_id' => $record->id,
            'attachment_id' => $attachment->id,
        ]);
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }

    /**
     * ⚠️ R03 : une notice d'une autre organisation ne doit exposer ni ses pièces
     * jointes (404 sur l'index), ni accepter un upload.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $record = $this->makeRecord($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/attachments")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/attachments/upload", [
                'file' => UploadedFile::fake()->create('intrusion.pdf', 10, 'application/pdf'),
            ])
            ->assertStatus(404);
    }
}
