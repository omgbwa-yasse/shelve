<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Met en phase le schéma Courrier d'evolution avec les fonctions validées sur main.
 *
 * La migration est volontairement défensive : elle peut être exécutée sur une base
 * issue de la baseline evolution ou sur une base ayant déjà reçu une partie des
 * migrations historiques de main.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addMailWorkflowColumns();
        $this->createOrganisationInterims();
        $this->createMailTransactions();
        $this->createMailCotations();
        $this->createMailCodeSequences();
        $this->ensureUniqueMailCodes();
    }

    private function addMailWorkflowColumns(): void
    {
        if (! Schema::hasTable('mails')) {
            return;
        }

        if (! Schema::hasColumn('mails', 'activity_id')) {
            Schema::table('mails', function (Blueprint $table) {
                $table->foreignId('activity_id')->nullable()->after('typology_id')
                    ->constrained('activities')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('mails', 'parent_mail_id')) {
            Schema::table('mails', function (Blueprint $table) {
                $table->foreignId('parent_mail_id')->nullable()->after('activity_id')
                    ->constrained('mails')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('mails', 'dg_signature_status')) {
            Schema::table('mails', function (Blueprint $table) {
                $table->string('dg_signature_status', 20)->nullable()->after('estimated_processing_time');
            });
        }

        if (! Schema::hasColumn('mails', 'dg_signed_by')) {
            Schema::table('mails', function (Blueprint $table) {
                $table->foreignId('dg_signed_by')->nullable()->after('dg_signature_status')
                    ->constrained('users')->nullOnDelete();
            });
        }

        foreach ([
            'dg_signed_at' => fn (Blueprint $table) => $table->timestamp('dg_signed_at')->nullable()->after('dg_signed_by'),
            'dg_signature_note' => fn (Blueprint $table) => $table->text('dg_signature_note')->nullable()->after('dg_signed_at'),
            'explanatory_note' => fn (Blueprint $table) => $table->text('explanatory_note')->nullable()->after('dg_signature_note'),
        ] as $column => $definition) {
            if (! Schema::hasColumn('mails', $column)) {
                Schema::table('mails', $definition);
            }
        }
    }

    private function createOrganisationInterims(): void
    {
        if (! Schema::hasTable('organisation_interims')) {
            Schema::create('organisation_interims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete();
                $table->foreignId('titular_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('interim_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
                $table->string('scope', 255)->nullable();
                $table->boolean('is_primary')->default(false);
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('reason')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(
                    ['organisation_id', 'titular_user_id', 'is_active'],
                    'org_interims_org_titular_active_idx'
                );
            });

            return;
        }

        foreach (['activity_id', 'scope', 'is_primary'] as $column) {
            if (Schema::hasColumn('organisation_interims', $column)) {
                continue;
            }

            Schema::table('organisation_interims', function (Blueprint $table) use ($column) {
                match ($column) {
                    'activity_id' => $table->foreignId('activity_id')->nullable()
                        ->constrained('activities')->nullOnDelete(),
                    'scope' => $table->string('scope', 255)->nullable(),
                    'is_primary' => $table->boolean('is_primary')->default(false),
                };
            });
        }
    }

    private function createMailTransactions(): void
    {
        if (Schema::hasTable('mail_transactions')) {
            return;
        }

        Schema::create('mail_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable();
            $table->dateTime('date_creation')->nullable();
            $table->foreignId('mail_id')->nullable()->constrained('mails')->cascadeOnDelete();
            $table->foreignId('user_send_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('organisation_send_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->foreignId('user_received_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('organisation_received_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->unsignedBigInteger('mail_type_id')->nullable();
            $table->unsignedBigInteger('document_type_id')->nullable();
            $table->unsignedBigInteger('action_id')->nullable();
            $table->boolean('to_return')->nullable()->default(false);
            $table->text('description')->nullable();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->timestamps();
        });
    }

    private function createMailCotations(): void
    {
        if (Schema::hasTable('mail_cotations')) {
            return;
        }

        Schema::create('mail_cotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_id')->constrained('mails')->cascadeOnDelete();
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete();
            $table->foreignId('action_id')->nullable()->constrained('mail_actions')->nullOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->text('instruction')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('coted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['mail_id', 'organisation_id'], 'mail_cotation_unique');
            $table->index(['organisation_id', 'status'], 'mail_cotation_org_status_idx');
        });
    }

    private function createMailCodeSequences(): void
    {
        if (Schema::hasTable('mail_code_sequences')) {
            return;
        }

        Schema::create('mail_code_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('register', 30);
            $table->unsignedSmallInteger('year');
            $table->foreignId('typology_id')->nullable()->constrained('mail_typologies')->nullOnDelete();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['register', 'year', 'typology_id'], 'mail_code_sequence_unique');
        });
    }

    private function ensureUniqueMailCodes(): void
    {
        if (! Schema::hasTable('mails')) {
            return;
        }

        $duplicates = DB::table('mails')
            ->select('code')
            ->whereNotNull('code')
            ->groupBy('code')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('code');

        foreach ($duplicates as $code) {
            $ids = DB::table('mails')->where('code', $code)->orderBy('id')->pluck('id')->slice(1);
            $suffix = 2;

            foreach ($ids as $id) {
                do {
                    $marker = '-D'.$suffix++;
                    $candidate = mb_substr($code, 0, 30 - mb_strlen($marker)).$marker;
                } while (DB::table('mails')->where('code', $candidate)->exists());

                DB::table('mails')->where('id', $id)->update(['code' => $candidate]);
            }
        }

        foreach (Schema::getIndexes('mails') as $index) {
            $name = is_array($index) ? ($index['name'] ?? null) : ($index->name ?? null);
            if ($name === 'mails_code_unique') {
                return;
            }
        }

        Schema::table('mails', function (Blueprint $table) {
            $table->unique('code', 'mails_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_cotations');
        Schema::dropIfExists('mail_code_sequences');
        Schema::dropIfExists('organisation_interims');

        if (Schema::hasTable('mails')) {
            Schema::table('mails', function (Blueprint $table) {
                foreach (['parent_mail_id', 'activity_id', 'dg_signed_by'] as $foreignId) {
                    if (Schema::hasColumn('mails', $foreignId)) {
                        $table->dropConstrainedForeignId($foreignId);
                    }
                }

                foreach (['dg_signature_status', 'dg_signed_at', 'dg_signature_note', 'explanatory_note'] as $column) {
                    if (Schema::hasColumn('mails', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
