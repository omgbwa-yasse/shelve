<?php

namespace Tests\Feature;

use App\Enums\MailStatusEnum;
use App\Models\Mail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MailWorkflowSchemaMigrationTest extends TestCase
{
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
            $table->string('name')->nullable();
            $table->foreignId('parent_id')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->foreignId('current_organisation_id')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id');
            $table->foreignId('role_id');
        });

        Schema::create('user_organisation_role', function (Blueprint $table) {
            $table->foreignId('user_id');
            $table->foreignId('organisation_id');
            $table->foreignId('role_id');
            $table->foreignId('creator_id')->nullable();
            $table->timestamps();
            $table->primary(['user_id', 'organisation_id']);
        });

        foreach (['activities', 'mail_actions', 'mail_typologies', 'batches'] as $tableName) {
            Schema::create($tableName, fn (Blueprint $table) => $table->id());
        }

        Schema::create('mails', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30);
            $table->string('name')->nullable();
            $table->dateTime('date')->nullable();
            $table->text('description')->nullable();
            $table->string('document_type')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('typology_id')->nullable();
            $table->unsignedBigInteger('action_id')->nullable();
            $table->unsignedBigInteger('sender_user_id')->nullable();
            $table->unsignedBigInteger('sender_organisation_id')->nullable();
            $table->unsignedBigInteger('recipient_user_id')->nullable();
            $table->unsignedBigInteger('recipient_organisation_id')->nullable();
            $table->string('mail_type')->default('internal');
            $table->boolean('is_archived')->default(false);
            $table->timestamp('deadline')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('assigned_organisation_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->unsignedInteger('estimated_processing_time')->nullable();
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

        DB::table('mails')->insert([
            ['code' => '2026/TEST/0001'],
            ['code' => '2026/TEST/0001'],
        ]);
    }

    public function test_mail_workflow_schema_is_complete_and_idempotent(): void
    {
        $migration = require database_path('migrations/2026_08_23_000000_sync_mail_workflow_from_main.php');

        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::hasTable('mail_cotations'));
        $this->assertTrue(Schema::hasTable('mail_code_sequences'));
        $this->assertTrue(Schema::hasTable('mail_transactions'));
        $this->assertTrue(Schema::hasTable('organisation_interims'));

        foreach ([
            'activity_id',
            'parent_mail_id',
            'dg_signature_status',
            'dg_signed_by',
            'dg_signed_at',
            'dg_signature_note',
            'explanatory_note',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('mails', $column), $column.' doit exister');
        }

        $this->assertCount(2, DB::table('mails')->distinct()->pluck('code'));
        $this->assertTrue(collect(Schema::getIndexes('mails'))->contains(function ($index) {
            $name = is_array($index) ? ($index['name'] ?? null) : ($index->name ?? null);

            return $name === 'mails_code_unique';
        }));
    }

    public function test_outgoing_mail_runs_through_n1_revision_and_dg_signature(): void
    {
        $this->runWorkflowMigration();
        $this->seedHierarchy();

        $mailId = DB::table('mails')->insertGetId([
            'code' => '2026/SORT/0001',
            'name' => 'Contrat fournisseur',
            'status' => MailStatusEnum::DRAFT->value,
            'mail_type' => Mail::TYPE_OUTGOING,
            'sender_user_id' => 1,
            'sender_organisation_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mail = Mail::findOrFail($mailId)->submitForApproval('Contrôle juridique effectué.');
        $this->assertSame(MailStatusEnum::PENDING_REVIEW, $mail->status);
        $this->assertSame(2, (int) $mail->assigned_to);

        $mail->validateBySuperior(2, 'Visa du directeur');
        $this->assertSame(MailStatusEnum::PENDING_APPROVAL, $mail->fresh()->status);

        $mail->returnForRevision(2, 'Corriger le montant');
        $this->assertSame(MailStatusEnum::REJECTED, $mail->fresh()->status);

        $mail->resubmit('Montant corrigé')->validateBySuperior(2);
        $mail->signByDg(3, 'Bon pour envoi');

        $mail = $mail->fresh();
        $this->assertSame(MailStatusEnum::TRANSMITTED, $mail->status);
        $this->assertSame('signed', $mail->dg_signature_status);
        $this->assertSame(3, (int) $mail->dg_signed_by);
        $this->assertNotNull($mail->processed_at);
        $this->assertTrue(DB::table('mail_histories')->where('mail_id', $mailId)->where('action', 'returned_for_revision')->exists());
        $this->assertTrue(DB::table('mail_histories')->where('mail_id', $mailId)->where('action', 'dg_signed')->exists());
    }

    public function test_multi_direction_cotation_closes_only_after_every_reception(): void
    {
        $this->runWorkflowMigration();
        $this->seedHierarchy();

        $mailId = DB::table('mails')->insertGetId([
            'code' => '2026/ENTR/0001',
            'name' => 'Demande externe',
            'status' => MailStatusEnum::TRANSMITTED->value,
            'mail_type' => Mail::TYPE_INCOMING,
            'recipient_organisation_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mail = Mail::findOrFail($mailId)->cote([2, 3], null, 'Préparer un avis', 3);
        $this->assertSame(2, $mail->cotations()->count());

        $mail->confirmReceptionForOrg(2, 2);
        $this->assertSame(MailStatusEnum::IN_PROGRESS, $mail->fresh()->status);
        $this->assertSame(1, $mail->cotations()->where('status', 'pending')->count());

        $mail->confirmReceptionForOrg(3, 3);
        $this->assertSame(MailStatusEnum::COMPLETED, $mail->fresh()->status);
        $this->assertSame(0, $mail->cotations()->where('status', 'pending')->count());
    }

    private function runWorkflowMigration(): void
    {
        (require database_path('migrations/2026_08_23_000000_sync_mail_workflow_from_main.php'))->up();
    }

    private function seedHierarchy(): void
    {
        DB::table('organisations')->insert([
            ['id' => 1, 'code' => 'DG', 'name' => 'Direction générale', 'parent_id' => null],
            ['id' => 2, 'code' => 'DSI', 'name' => 'Direction informatique', 'parent_id' => 1],
            ['id' => 3, 'code' => 'FIN', 'name' => 'Direction financière', 'parent_id' => 1],
        ]);
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'agent'],
            ['id' => 2, 'name' => 'directeur'],
            ['id' => 3, 'name' => 'DG'],
        ]);
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Agent', 'surname' => 'DSI', 'current_organisation_id' => 2],
            ['id' => 2, 'name' => 'Directeur', 'surname' => 'DSI', 'current_organisation_id' => 2],
            ['id' => 3, 'name' => 'Directeur', 'surname' => 'Général', 'current_organisation_id' => 1],
        ]);
        DB::table('user_organisation_role')->insert([
            ['user_id' => 1, 'organisation_id' => 2, 'role_id' => 1],
            ['user_id' => 2, 'organisation_id' => 2, 'role_id' => 2],
            ['user_id' => 3, 'organisation_id' => 1, 'role_id' => 3],
        ]);
    }
}
