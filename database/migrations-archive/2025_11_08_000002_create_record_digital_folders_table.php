<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('record_digital_folders', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('code')->unique()->comment('Code unique généré selon le pattern du type');
            $table->string('name')->comment('Nom du dossier');
            $table->text('description')->nullable()->comment('Description détaillée');

            // Type et hiérarchie
            $table->foreignId('type_id')
                ->constrained('record_digital_folder_types')
                ->comment('Type de dossier');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('record_digital_folders')
                ->onDelete('cascade')
                ->comment('Dossier parent pour hiérarchie');

            // Métadonnées et configuration
            $table->json('metadata')->nullable()->comment('Métadonnées personnalisées selon le type');
            $table->enum('access_level', ['public', 'internal', 'confidential', 'secret'])
                ->default('internal')
                ->comment('Niveau d\'accès au dossier');
            $table->enum('status', ['active', 'archived', 'closed'])
                ->default('active')
                ->comment('Statut du dossier');

            // Workflow et approbation
            $table->boolean('requires_approval')->default(false)->comment('Nécessite approbation');
            $table->foreignId('approved_by')->nullable()->constrained('users')->comment('Approuvé par');
            $table->timestamp('approved_at')->nullable()->comment('Date d\'approbation');
            $table->text('approval_notes')->nullable()->comment('Notes d\'approbation');

            // Relations organisationnelles
            $table->foreignId('creator_id')->constrained('users')->comment('Créateur du dossier');
            $table->foreignId('organisation_id')->constrained('organisations')->comment('Organisation propriétaire');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->comment('Responsable assigné');

            // Statistiques
            $table->integer('documents_count')->default(0)->comment('Nombre de documents');
            $table->integer('subfolders_count')->default(0)->comment('Nombre de sous-dossiers');
            $table->bigInteger('total_size')->default(0)->comment('Taille totale en octets');

            // Dates et timestamps
            $table->date('start_date')->nullable()->comment('Date de début');
            $table->date('end_date')->nullable()->comment('Date de fin');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type_id');
            $table->index('parent_id');
            $table->index('status');
            $table->index('creator_id');
            $table->index('organisation_id');
            $table->index(['type_id', 'status']);
            $table->index(['organisation_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_digital_folders');
    }
};
