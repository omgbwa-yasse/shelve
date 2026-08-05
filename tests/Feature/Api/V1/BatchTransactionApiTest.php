<?php

namespace Tests\Feature\Api\V1;

use App\Models\Batch;
use App\Models\BatchTransaction;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D06 — transactions de parapheur (org-scopées via `organisation_send_id` /
 * `organisation_received_id`). Portage finalisé le 2026-08-04.
 */
class BatchTransactionApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['batch_transaction', 'batch'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeTransaction(
        Organisation $sender = null,
        Organisation $receiver = null,
        Organisation $holder = null
    ): BatchTransaction {
        $sender = $sender ?? $this->organisation;
        $receiver = $receiver ?? Organisation::factory()->create();
        $holder = $holder ?? $sender;

        $batch = Batch::create([
            'code' => 'B' . substr(uniqid(), -6),
            'name' => 'Parapheur',
            'organisation_holder_id' => $holder->id,
        ]);

        return BatchTransaction::create([
            'batch_id' => $batch->id,
            'organisation_send_id' => $sender->id,
            'organisation_received_id' => $receiver->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/batch-transactions')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/batch-transactions')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_transactions_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeTransaction($this->organisation, $orgEtrangere);   // je suis émetteur
        $this->makeTransaction($orgEtrangere, $orgEtrangere);         // je n'y suis pas

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/batch-transactions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $transaction = $this->makeTransaction($this->organisation);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/batch-transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $transaction->id);
    }

    public function test_store_n_est_pas_expose(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/batch-transactions', ['batch_id' => 1])
            ->assertStatus(501);
    }

    public function test_update_modifie_le_parapheur_lie(): void
    {
        $transaction = $this->makeTransaction($this->organisation);
        $nouveauBatch = Batch::create([
            'code' => 'B' . substr(uniqid(), -6),
            'name' => 'Autre parapheur',
            'organisation_holder_id' => $this->organisation->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/batch-transactions/{$transaction->id}", ['batch_id' => $nouveauBatch->id])
            ->assertOk()
            ->assertJsonPath('data.batch_id', $nouveauBatch->id);
    }

    public function test_destroy_supprime_la_transaction(): void
    {
        $transaction = $this->makeTransaction($this->organisation);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/batch-transactions/{$transaction->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('batch_transactions', ['id' => $transaction->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : une transaction d'autres organisations doit renvoyer 404
     * (jamais 403 — un 403 confirmerait son existence), sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $transaction = $this->makeTransaction($orgEtrangere, $orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/batch-transactions/{$transaction->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/batch-transactions/{$transaction->id}", ['batch_id' => $transaction->batch_id])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/batch-transactions/{$transaction->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('batch_transactions', ['id' => $transaction->id]);
    }
}
