<?php

namespace Tests\Feature\Api\V1;

use App\Models\Author;
use App\Models\AuthorType;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D02 — pivot notice ↔ auteur (`record_author`, org-scopée par la notice parente,
 * motif D03). Portage finalisé le 2026-08-05 : ressource imbriquée sous
 * `/records/{record}/authors`, Policy `AuthorPolicy` (D01, préfixe `author_*`).
 */
class RecordAuthorApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['author'];

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

    private function makeAuthor(): Author
    {
        $type = AuthorType::first() ?? AuthorType::create([
            'name' => 'Personne',
            'description' => 'Type test',
        ]);

        return Author::create([
            'type_id' => $type->id,
            'name' => 'Auteur ' . substr(uniqid(), -6),
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/records/1/authors')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $record = $this->makeRecord();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/authors")
            ->assertStatus(403);
    }

    public function test_index_retourne_les_auteurs_de_la_notice(): void
    {
        $record = $this->makeRecord();
        $author = $this->makeAuthor();
        $record->authors()->attach($author->id);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/authors")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $author->id);
    }

    public function test_store_associe_un_auteur_a_la_notice(): void
    {
        $record = $this->makeRecord();
        $author = $this->makeAuthor();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/authors", ['author_id' => $author->id])
            ->assertStatus(201)
            ->assertJsonPath('data.id', $author->id);

        $this->assertDatabaseHas('record_author', [
            'record_id' => $record->id,
            'author_id' => $author->id,
        ]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $record = $this->makeRecord();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/authors", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('author_id');
    }

    public function test_destroy_detache_l_auteur(): void
    {
        $record = $this->makeRecord();
        $author = $this->makeAuthor();
        $record->authors()->attach($author->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}/authors/{$author->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('record_author', [
            'record_id' => $record->id,
            'author_id' => $author->id,
        ]);
    }

    /**
     * ⚠️ R03 : une notice d'une autre organisation ne doit exposer ni ses auteurs
     * (404 sur l'index), ni accepter une association.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $record = $this->makeRecord($orgEtrangere);
        $author = $this->makeAuthor();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/authors")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/authors", ['author_id' => $author->id])
            ->assertStatus(404);

        $this->assertDatabaseMissing('record_author', ['record_id' => $record->id, 'author_id' => $author->id]);
    }
}
