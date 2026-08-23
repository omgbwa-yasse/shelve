<?php

namespace Tests\Feature;

use App\Enums\MailStatusEnum;
use App\Models\Mail;
use App\Models\MailCircuit;
use App\Models\MailCircuitStep;
use App\Models\User;
use App\Services\Mail\MailCircuitService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MailCircuitServiceTest extends TestCase
{
    private MailCircuitService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        Mail::disableSearchSyncing();

        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->foreignId('parent_id')->nullable();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->foreignId('current_organisation_id')->nullable();
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('user_organisation_role', function (Blueprint $table) {
            $table->foreignId('user_id');
            $table->foreignId('organisation_id');
            $table->foreignId('role_id');
            $table->foreignId('creator_id')->nullable();
            $table->timestamps();
            $table->primary(['user_id', 'organisation_id']);
        });
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id');
            $table->foreignId('role_id');
        });
        foreach (['activities', 'mail_actions', 'mail_typologies'] as $tableName) {
            Schema::create($tableName, fn (Blueprint $table) => $table->id());
        }
        Schema::create('mails', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name')->nullable();
            $table->dateTime('date')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->string('mail_type')->default('outgoing');
            $table->foreignId('sender_user_id')->nullable();
            $table->foreignId('sender_organisation_id')->nullable();
            $table->foreignId('recipient_user_id')->nullable();
            $table->foreignId('recipient_organisation_id')->nullable();
            $table->foreignId('assigned_to')->nullable();
            $table->foreignId('assigned_organisation_id')->nullable();
            $table->foreignId('activity_id')->nullable();
            $table->string('explanatory_note')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('mail_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_id');
            $table->foreignId('user_id')->nullable();
            $table->string('action');
            $table->string('field_changed')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('location_data')->nullable();
            $table->unsignedInteger('processing_time')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        (require database_path('migrations/2026_08_23_010000_create_mail_circuit_tables.php'))->up();

        DB::table('organisations')->insert([
            ['id' => 1, 'code' => 'DG', 'name' => 'Direction générale', 'parent_id' => null],
            ['id' => 2, 'code' => 'MET', 'name' => 'Service métier', 'parent_id' => 1],
        ]);
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Initiateur', 'current_organisation_id' => 2],
            ['id' => 2, 'name' => 'N1', 'current_organisation_id' => 2],
            ['id' => 3, 'name' => 'Finance', 'current_organisation_id' => 1],
            ['id' => 4, 'name' => 'Juridique', 'current_organisation_id' => 1],
            ['id' => 5, 'name' => 'DSI', 'current_organisation_id' => 1],
            ['id' => 6, 'name' => 'DG', 'current_organisation_id' => 1],
            ['id' => 7, 'name' => 'Suppléant', 'current_organisation_id' => 2],
        ]);

        $this->service = app(MailCircuitService::class);
    }

    public function test_contract_waits_for_all_parallel_opinions_before_dg(): void
    {
        $mail = $this->mail('2026/CTR/001');
        $circuit = $this->service->start($mail, 'contract', User::find(1), [
            'validators' => ['n1' => 2, 'finance' => 3, 'legal' => 4, 'dg' => 6],
        ]);

        $this->assertSame(
            MailStatusEnum::PENDING_REVIEW->value,
            $mail->histories()->where('action', 'circuit_started')->firstOrFail()->new_value
        );
        $this->assertSame('pending', $circuit->steps->firstWhere('step_key', 'n1')->status);
        $this->service->act($circuit->steps->firstWhere('step_key', 'n1'), 'approve', User::find(2));

        $circuit->refresh()->load('steps');
        $this->assertSame('pending', $circuit->steps->firstWhere('step_key', 'finance')->status);
        $this->assertSame('pending', $circuit->steps->firstWhere('step_key', 'legal')->status);
        $this->assertSame('waiting', $circuit->steps->firstWhere('step_key', 'dg')->status);

        $this->service->act($circuit->steps->firstWhere('step_key', 'finance'), 'approve', User::find(3));
        $this->assertSame('waiting', $circuit->fresh()->steps->firstWhere('step_key', 'dg')->status);

        $this->service->act($circuit->fresh()->steps->firstWhere('step_key', 'legal'), 'approve_with_comment', User::find(4), 'Conforme.');
        $this->assertSame('pending', $circuit->fresh()->steps->firstWhere('step_key', 'dg')->status);
    }

    public function test_revision_resets_every_approval_and_keeps_versioned_history(): void
    {
        $mail = $this->mail('2026/REV/001');
        $circuit = $this->service->start($mail, 'n1', User::find(1), ['validators' => ['n1' => 2]]);
        $step = $circuit->steps->first();

        $this->service->act($step, 'revision', User::find(2), 'Corriger la pièce jointe.');
        $this->assertSame(MailCircuit::STATUS_REVISION, $circuit->fresh()->status);
        $this->assertSame(MailStatusEnum::REJECTED, $mail->fresh()->status);

        $this->service->resumeAfterRevision($circuit->fresh(), User::find(1), 'Pièce remplacée.');
        $circuit->refresh()->load('steps');

        $this->assertSame(2, $circuit->version);
        $this->assertSame(MailCircuitStep::STATUS_PENDING, $circuit->steps->first()->status);
        $this->assertTrue($circuit->actions()->where('version', 1)->where('action', 'revision_requested')->exists());
        $this->assertTrue($circuit->actions()->where('version', 2)->where('action', 'resubmitted')->exists());
    }

    public function test_conditional_external_request_skips_unused_specialists(): void
    {
        $mail = $this->mail('2026/EXT/001');
        $circuit = $this->service->start($mail, 'external_request', User::find(1), [
            'official_response' => true,
            'validators' => ['n1' => 2, 'dg' => 6],
        ]);

        $this->assertSame('skipped', $circuit->steps->firstWhere('step_key', 'finance')->status);
        $this->assertSame('skipped', $circuit->steps->firstWhere('step_key', 'legal')->status);
        $this->assertSame('skipped', $circuit->steps->firstWhere('step_key', 'dsi')->status);

        $this->service->act($circuit->steps->firstWhere('step_key', 'n1'), 'approve', User::find(2));
        $this->assertSame('pending', $circuit->fresh()->steps->firstWhere('step_key', 'dg')->status);
    }

    public function test_delegation_and_escalation_are_traced(): void
    {
        $mail = $this->mail('2026/DEL/001');
        $circuit = $this->service->start($mail, 'n1', User::find(1), ['validators' => ['n1' => 2]]);
        $step = $circuit->steps->first();

        $this->service->delegate($step, User::find(7), User::find(2), 'Absence planifiée.');
        $this->assertSame(7, (int) $step->fresh()->assigned_user_id);

        $this->service->escalate($step->fresh(), User::find(6), User::find(1), 'Délai critique.');
        $this->assertSame(6, (int) $step->fresh()->assigned_user_id);
        $this->assertTrue($circuit->actions()->where('action', 'delegated')->exists());
        $this->assertTrue($circuit->actions()->where('action', 'escalated')->exists());
    }

    public function test_completed_circuit_must_be_transmitted_explicitly(): void
    {
        $mail = $this->mail('2026/FIN/001');
        $circuit = $this->service->start($mail, 'n1', User::find(1), ['validators' => ['n1' => 2]]);
        $this->service->act($circuit->steps->first(), 'approve', User::find(2));

        $this->assertSame(MailCircuit::STATUS_COMPLETED, $circuit->fresh()->status);
        $this->assertSame(MailStatusEnum::APPROVED, $mail->fresh()->status);

        $this->service->transmit($circuit->fresh(), User::find(1), 'Envoyé au partenaire.');
        $this->assertSame(MailStatusEnum::TRANSMITTED, $mail->fresh()->status);
    }

    private function mail(string $code): Mail
    {
        return Mail::create([
            'code' => $code,
            'name' => 'Courrier de test',
            'status' => MailStatusEnum::DRAFT,
            'mail_type' => Mail::TYPE_OUTGOING,
            'sender_user_id' => 1,
            'sender_organisation_id' => 2,
        ]);
    }
}
