<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Mail;
use App\Models\MailContainer;
use App\Models\Dolly;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchTransferTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_transfer_to_boxes_validation_error_when_missing_payload(): void
    {
        $user = User::factory()->create();
        $batch = Batch::factory()->create();

        $this->actingAs($user)
            ->postJson("/mails/batches/{$batch->id}/transfer/boxes", [])
            ->assertStatus(422);
    }

    public function test_transfer_to_dollies_validation_error_when_missing_payload(): void
    {
        $user = User::factory()->create();
        $batch = Batch::factory()->create();

        $this->actingAs($user)
            ->postJson("/mails/batches/{$batch->id}/transfer/dollies", [])
            ->assertStatus(422);
    }

    public function test_transfer_to_boxes_happy_path_returns_200_and_creates_archives(): void
    {
        $user = User::factory()->create();
        // Grant broad ability by flagging user as superadmin if helpers available
        // or rely on policies mapping 'is-superadmin' gate to role name 'superadmin'
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('superadmin');
        }

        $batch = Batch::factory()->create();
        $mails = \App\Models\Mail::factory()->count(2)->create();
        // Attach mails to batch
        $batch->mails()->attach($mails->pluck('id')->all(), ['insert_date' => now()]);

        $boxes = \App\Models\MailContainer::factory()->count(2)->create();

        $res = $this->actingAs($user)
            ->postJson("/mails/batches/{$batch->id}/transfer/boxes", [
                'mail_ids' => $mails->pluck('id')->all(),
                'box_ids' => $boxes->pluck('id')->all(),
            ]);

        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
                'transferred' => 4, // 2 mails x 2 boxes
            ]);

        // Verify pivot entries exist
        foreach ($boxes as $box) {
            foreach ($mails as $mail) {
                $this->assertDatabaseHas('mail_archives', [
                    'container_id' => $box->id,
                    'mail_id' => $mail->id,
                    'document_type' => 'original',
                ]);
            }
        }
    }

    public function test_transfer_to_dollies_happy_path_returns_200_and_creates_pivots(): void
    {
        $user = User::factory()->create();
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('superadmin');
        }

        $batch = Batch::factory()->create();
        $mails = \App\Models\Mail::factory()->count(3)->create();
        $batch->mails()->attach($mails->pluck('id')->all(), ['insert_date' => now()]);

        $dollies = \App\Models\Dolly::factory()->count(1)->create();

        $res = $this->actingAs($user)
            ->postJson("/mails/batches/{$batch->id}/transfer/dollies", [
                'mail_ids' => $mails->pluck('id')->all(),
                'dolly_ids' => $dollies->pluck('id')->all(),
            ]);

        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
                'transferred' => 3, // 3 mails x 1 dolly
            ]);

        foreach ($dollies as $dolly) {
            foreach ($mails as $mail) {
                $this->assertDatabaseHas('dolly_mails', [
                    'dolly_id' => $dolly->id,
                    'mail_id' => $mail->id,
                ]);
            }
        }
    }

    public function test_transfer_membership_violation_returns_422_with_invalid_ids(): void
    {
        $user = User::factory()->create();
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('superadmin');
        }

        $batch = Batch::factory()->create();
        $mailsInBatch = \App\Models\Mail::factory()->count(1)->create();
        $batch->mails()->attach($mailsInBatch->pluck('id')->all(), ['insert_date' => now()]);

        $mailOutside = \App\Models\Mail::factory()->create();
        $box = \App\Models\MailContainer::factory()->create();

        $res = $this->actingAs($user)
            ->postJson("/mails/batches/{$batch->id}/transfer/boxes", [
                'mail_ids' => [$mailsInBatch->first()->id, $mailOutside->id],
                'box_ids' => [$box->id],
            ]);

        $res->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])->assertJsonStructure(['invalid_mail_ids']);
    }

    public function test_transfer_size_limit_violation_returns_422(): void
    {
        $user = User::factory()->create();
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('superadmin');
        }

        $batch = Batch::factory()->create();
        // Create mails beyond the MAX_MAILS_PER_REQUEST=500 by 1
        $count = 501;
        $mails = \App\Models\Mail::factory()->count($count)->create();
        $batch->mails()->attach($mails->pluck('id')->all(), ['insert_date' => now()]);
        $box = \App\Models\MailContainer::factory()->create();

        $res = $this->actingAs($user)
            ->postJson("/mails/batches/{$batch->id}/transfer/boxes", [
                'mail_ids' => $mails->pluck('id')->all(),
                'box_ids' => [$box->id],
            ]);

        $res->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
