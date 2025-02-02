<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
        {

            // Workflow step types table
            Schema::create('workflow_step_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->foreignId('organization_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('workflow_id')->constrained('workflows');
                $table->integer('default_order')->nullable();
                $table->integer('estimated_duration')->nullable(); // in minutes
                $table->json('required_skills')->nullable();
                $table->timestamps();
                $table->unique(['name', 'organization_id']);
            });

            // Main workflows table
            Schema::create('workflows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
                $table->string('status', 50);
                $table->timestamp('start_date')->useCurrent();
                $table->timestamp('end_date')->nullable();
                $table->foreignId('responsible_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('comment')->nullable();
                $table->integer('priority')->default(1);
                $table->integer('estimated_time')->nullable(); // in minutes
                $table->integer('actual_time')->nullable(); // in minutes
                $table->boolean('requires_validation')->default(false);
                $table->foreignId('validator_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('validation_date')->nullable();
                $table->string('workflow_model', 100)->nullable();
                $table->integer('version')->default(1);
                $table->timestamps();
            });

            // Workflow interventions table
            Schema::create('workflow_interventions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
                $table->foreignId('step_type_id')->nullable()->constrained('workflow_step_types');
                $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('organization_id')->constrained('organizations');
                $table->integer('execution_order');
                $table->string('status', 50)->default('pending');
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->integer('estimated_time')->nullable(); // in minutes
                $table->integer('actual_time')->nullable(); // in minutes
                $table->text('comment')->nullable();
                $table->json('required_documents')->nullable();
                $table->json('produced_documents')->nullable();
                $table->boolean('requires_validation')->default(false);
                $table->foreignId('validator_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('validation_date')->nullable();
                $table->timestamps();
            });



            // Workflow transitions table
            Schema::create('workflow_transitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
                $table->foreignId('source_intervention_id')->nullable()->constrained('workflow_interventions');
                $table->foreignId('destination_intervention_id')->nullable()->constrained('workflow_interventions');
                $table->text('transition_conditions')->nullable();
                $table->boolean('is_automatic')->default(false);
                $table->boolean('requires_notification')->default(true);
                $table->integer('maximum_delay')->nullable(); // in minutes
                $table->timestamps();
                $table->unique(
                    ['workflow_id', 'source_intervention_id', 'destination_intervention_id'],
                    'unique_transition'
                );
            });
        }

        public function down()
        {
            Schema::dropIfExists('workflow_transitions');
            Schema::dropIfExists('workflow_interventions');
            Schema::dropIfExists('workflows');
            Schema::dropIfExists('workflow_step_types');
            Schema::dropIfExists('organizations');
        }
};
