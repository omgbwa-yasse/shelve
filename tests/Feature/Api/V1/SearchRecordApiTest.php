<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D10 — Recherche de notices (domaine d'actions, org-scopé via `records.organisation_id`).
 * Portage finalisé le 2026-08-05.
 */
class SearchRecordApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        // La Policy Record utilise la permission historique `records_view`.
        $this->grantD01Permissions($this->user, ['records'], ['view']);
    }

    private function makeRecord(Organisation $org, array $extra = []): Record
    {
        $level = RecordLevel::firstOrCreate(['name' => 'Dossier']);
        $status = RecordStatus::firstOrCreate(['name' => 'Brouillon']);

        return Record::create([
            'code' => 'R' . substr(uniqid(), -6),
            'name' => 'Notice de recherche',
            'level_id' => $level->id,
            'status_id' => $status->id,
            'organisation_id' => $org->id,
            'creator_id' => $this->user->id,
            'is_current_version' => true,
            ...$extra,
        ]);
    }

    public function test_search_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/search/records?q=contrat')->assertStatus(401);
    }

    public function test_search_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/search/records?q=contrat')
            ->assertStatus(403);
    }

    public function test_search_avec_q_retourne_l_enveloppe_paginee(): void
    {
        $record = $this->makeRecord($this->organisation, ['name' => 'Contrat de bail unique']);
        $this->makeRecord($this->organisation, ['name' => 'Autre dossier']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/search/records?q=contrat')
            ->assertOk();

        $response->assertJsonStructure([
            'data' => ['*' => ['id', 'code', 'name', 'organisation_id']],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            'links' => ['first', 'prev', 'next', 'last'],
        ]);

        $this->assertContains(
            $record->id,
            collect($response->json('data'))->pluck('id')->all()
        );
    }

    public function test_search_respecte_l_organisation_courante(): void
    {
        $orgB = Organisation::factory()->create();
        $this->makeRecord($orgB, ['name' => 'Contrat xyzzy-secret autre organisation']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/search/records?q=xyzzy+secret')
            ->assertOk()
            ->assertJsonPath('meta.total', 0)
            ->assertJsonCount(0, 'data');
    }
}
