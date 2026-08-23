<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            if (! Schema::hasColumn('mails', 'confidentiality_level')) {
                $table->string('confidentiality_level', 30)->default('normal')->after('explanatory_note');
            }
            if (! Schema::hasColumn('mails', 'access_restricted')) {
                $table->boolean('access_restricted')->default(false)->after('confidentiality_level');
            }
        });

        if (! Schema::hasTable('mail_circuits')) {
            Schema::create('mail_circuits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mail_id')->constrained('mails')->cascadeOnDelete();
                $table->string('template_code', 60);
                $table->string('template_name');
                $table->string('status', 40)->default('active')->index();
                $table->string('mode', 20)->default('sequential');
                $table->unsignedSmallInteger('version')->default(1);
                $table->json('metadata')->nullable();
                $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['mail_id', 'status']);
            });
        }

        if (! Schema::hasTable('mail_circuit_steps')) {
            Schema::create('mail_circuit_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mail_circuit_id')->constrained('mail_circuits')->cascadeOnDelete();
                $table->string('step_key', 80);
                $table->string('label');
                $table->unsignedSmallInteger('stage')->default(1)->index();
                $table->unsignedSmallInteger('sequence')->default(1);
                $table->string('validator_type', 40)->default('user');
                $table->string('validator_value')->nullable();
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('original_assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('delegated_from_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 40)->default('waiting')->index();
                $table->boolean('is_required')->default(true);
                $table->boolean('is_parallel')->default(false);
                $table->string('condition_key', 80)->nullable();
                $table->timestamp('due_at')->nullable()->index();
                $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('acted_at')->nullable();
                $table->text('comment')->nullable();
                $table->timestamps();

                $table->unique(['mail_circuit_id', 'step_key'], 'mail_circuit_step_unique');
                $table->index(['mail_circuit_id', 'stage', 'status'], 'mail_circuit_stage_status');
            });
        }

        if (! Schema::hasTable('mail_circuit_actions')) {
            Schema::create('mail_circuit_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mail_circuit_id')->constrained('mail_circuits')->cascadeOnDelete();
                $table->foreignId('mail_circuit_step_id')->nullable()->constrained('mail_circuit_steps')->nullOnDelete();
                $table->unsignedSmallInteger('version')->default(1);
                $table->string('action', 60)->index();
                $table->string('from_status', 40)->nullable();
                $table->string('to_status', 40)->nullable();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('comment')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['mail_circuit_id', 'created_at'], 'mail_circuit_action_timeline');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_circuit_actions');
        Schema::dropIfExists('mail_circuit_steps');
        Schema::dropIfExists('mail_circuits');

        Schema::table('mails', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['confidentiality_level', 'access_restricted'],
                fn (string $column) => Schema::hasColumn('mails', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
