<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 100);
            $table->string('code', 20)->unique()->index();
            $table->text('description')->nullable();
            $table->date('start_date')->index();  // Optimisé: datetime -> date
            $table->date('end_date')->index();    // Optimisé: datetime -> date
            $table->enum('status', ['draft', 'active', 'on_hold', 'completed', 'cancelled'])
                  ->default('draft')
                  ->index();
            $table->text('objectives')->nullable();
            $table->decimal('budget', 13, 2)->nullable();          // Optimisé: 15,2 -> 13,2
            $table->decimal('actual_cost', 13, 2)->nullable();     // Optimisé: 15,2 -> 13,2
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('project_supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', [
                'project_manager',
                'technical_supervisor',
                'business_supervisor',
                'quality_supervisor'
            ]);
            $table->date('assigned_at');         // Optimisé: datetime -> date
            $table->date('end_date')->nullable(); // Optimisé: datetime -> date
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();
            $table->index(['user_id', 'role']);
        });

        Schema::create('project_resource_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->enum('category', ['human', 'material', 'service', 'software'])
                  ->index();
            $table->text('description')->nullable();
            $table->json('attributes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index(['is_active', 'category']);
        });

        Schema::create('project_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_type_id')->constrained('project_resource_types')->onDelete('restrict');
            $table->string('code', 20)->unique()->index();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->json('specifications')->nullable();
            $table->boolean('is_available')->default(true);
            $table->decimal('cost_rate', 10, 2)->nullable();
            $table->string('unit', 20)->nullable();
            $table->unsignedTinyInteger('max_allocation_percentage')->default(100); // Optimisé: integer -> tinyInteger
            $table->json('availability_schedule')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Index optimisé pour la recherche de ressources disponibles
            $table->index(['is_available', 'resource_type_id']);
            $table->check('max_allocation_percentage BETWEEN 0 AND 100');
        });

        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_task_id')->nullable()->constrained('project_tasks')->onDelete('restrict');
            $table->string('code', 20)->unique()->index();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->date('start_date')->index();  // Optimisé: datetime -> date
            $table->date('end_date')->index();    // Optimisé: datetime -> date
            $table->unsignedInteger('estimated_hours')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                  ->default('medium')
                  ->index();
            $table->enum('status', [
                'not_started',
                'in_progress',
                'completed',
                'blocked',
                'under_review',
                'cancelled'
            ])->default('not_started');
            $table->unsignedTinyInteger('progress_percentage')->default(0); // Optimisé: integer -> tinyInteger
            $table->json('dependencies')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Index optimisés
            $table->index(['status', 'priority', 'start_date']);
            $table->index(['project_id', 'status']);

            // Contraintes de validation
            $table->check('progress_percentage BETWEEN 0 AND 100');
        });


        Schema::create('project_task_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('project_tasks')->onDelete('cascade');
            $table->string('version', 20)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', [
                'draft',
                'submitted',
                'in_review',
                'approved',
                'rejected',
                'archived'
            ])->default('draft')->index();
            $table->foreignId('submitted_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('submitted_at')->nullable()->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_comments')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index optimisé pour la recherche de livrables
            $table->index(['task_id', 'status']);
        });


        Schema::create('project_task_deliverable_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('project_task_deliverables')->onDelete('cascade');
            $table->foreignId('attachment_id')->constrained('attachments')->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            // Index optimisé
            $table->index(['task_id', 'created_at']);
        });


        Schema::create('project_task_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('resource_id')->constrained('project_resources')->onDelete('restrict');
            $table->date('start_date')->index();  // Optimisé: datetime -> date
            $table->date('end_date')->index();    // Optimisé: datetime -> date
            $table->unsignedInteger('allocated_hours')->nullable();
            $table->decimal('allocated_percentage', 5, 2)->default(100.00);
            $table->decimal('cost_override', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('allocated_by')->constrained('users');
            $table->timestamps();

            $table->unique(['task_id', 'resource_id', 'start_date', 'end_date'], 'task_resource_period_unique');
            $table->index(['resource_id', 'start_date', 'end_date'], 'resource_period_index');

            // Contraintes de validation
            $table->check('start_date <= end_date');
            $table->check('allocated_percentage BETWEEN 0 AND 100');
            $table->check('allocated_hours >= 0');
            $table->check('cost_override >= 0');
        });


        Schema::create('project_task_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('task_id')->constrained('project_tasks')->onDelete('cascade');
            $table->foreignId('resource_id')->nullable()->constrained('project_resources')->onDelete('restrict');
            $table->foreignId('logged_by')->constrained('users')->onDelete('restrict');
            $table->date('work_date')->index();   // Déjà optimisé
            $table->unsignedSmallInteger('hours_spent');          // Optimisé: integer -> smallInteger
            $table->unsignedTinyInteger('progress_percentage');   // Optimisé: integer -> tinyInteger
            $table->text('work_description')->nullable();
            $table->text('issues')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_billable')->default(true)->index();
            $table->timestamps();

            $table->unique(['task_id', 'resource_id', 'work_date', 'logged_by'], 'unique_daily_log');

            // Index optimisés
            $table->index(['task_id', 'work_date']);
            $table->index(['resource_id', 'work_date']);

            // Contraintes de validation
            $table->check('hours_spent > 0');
            $table->check('progress_percentage BETWEEN 0 AND 100');
        });

    }

    public function down(): void
    {
        // Suppression dans l'ordre inverse pour respecter les contraintes de clés étrangères
        Schema::dropIfExists('project_task_logs');
        Schema::dropIfExists('project_task_resources');
        Schema::dropIfExists('project_task_deliverable_attachments');
        Schema::dropIfExists('project_task_deliverables');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_resources');
        Schema::dropIfExists('project_resource_types');
        Schema::dropIfExists('project_supervisors');
        Schema::dropIfExists('projects');
    }
};
