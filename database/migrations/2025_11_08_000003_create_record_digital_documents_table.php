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
        Schema::create('record_digital_documents', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('code')->unique()->comment('Code unique généré selon le pattern du type');
            $table->string('name')->comment('Nom du document');
            $table->text('description')->nullable()->comment('Description détaillée');

            // Type et classement
            $table->foreignId('type_id')
                ->constrained('record_digital_document_types')
                ->comment('Type de document');
            $table->foreignId('folder_id')
                ->nullable()
                ->constrained('record_digital_folders')
                ->onDelete('cascade')
                ->comment('Dossier parent');

            // Fichier attaché (lien vers attachments)
            $table->foreignId('attachment_id')
                ->nullable()
                ->constrained('attachments')
                ->comment('Fichier attaché principal');

            // Versioning
            $table->integer('version_number')->default(1)->comment('Numéro de version');
            $table->boolean('is_current_version')->default(true)->comment('Version courante');
            $table->foreignId('parent_version_id')
                ->nullable()
                ->constrained('record_digital_documents')
                ->comment('Version parente');
            $table->text('version_notes')->nullable()->comment('Notes de version');

            // Check-out/Check-in
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->comment('Réservé par');
            $table->timestamp('checked_out_at')->nullable()->comment('Date de réservation');

            // Signature électronique
            $table->enum('signature_status', ['unsigned', 'pending', 'signed', 'rejected'])
                ->default('unsigned')
                ->comment('Statut de signature');
            $table->foreignId('signed_by')->nullable()->constrained('users')->comment('Signé par');
            $table->timestamp('signed_at')->nullable()->comment('Date de signature');
            $table->text('signature_data')->nullable()->comment('Données de signature (hash, certificat)');

            // Métadonnées et configuration
            $table->json('metadata')->nullable()->comment('Métadonnées personnalisées selon le type');
            $table->enum('access_level', ['public', 'internal', 'confidential', 'secret'])
                ->default('internal')
                ->comment('Niveau d\'accès');
            $table->enum('status', ['draft', 'active', 'archived', 'obsolete'])
                ->default('draft')
                ->comment('Statut du document');

            // Workflow et approbation
            $table->boolean('requires_approval')->default(false)->comment('Nécessite approbation');
            $table->foreignId('approved_by')->nullable()->constrained('users')->comment('Approuvé par');
            $table->timestamp('approved_at')->nullable()->comment('Date d\'approbation');
            $table->text('approval_notes')->nullable()->comment('Notes d\'approbation');

            // Rétention et archivage
            $table->date('retention_until')->nullable()->comment('Date de fin de rétention');
            $table->boolean('is_archived')->default(false)->comment('Document archivé');
            $table->timestamp('archived_at')->nullable()->comment('Date d\'archivage');

            // Relations organisationnelles
            $table->foreignId('creator_id')->constrained('users')->comment('Créateur');
            $table->foreignId('organisation_id')->constrained('organisations')->comment('Organisation');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->comment('Responsable');

            // Statistiques
            $table->integer('download_count')->default(0)->comment('Nombre de téléchargements');
            $table->timestamp('last_viewed_at')->nullable()->comment('Dernière consultation');
            $table->foreignId('last_viewed_by')->nullable()->constrained('users')->comment('Dernière consultation par');

            // Dates
            $table->date('document_date')->nullable()->comment('Date du document');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type_id');
            $table->index('folder_id');
            $table->index('attachment_id');
            $table->index('status');
            $table->index('signature_status');
            $table->index('creator_id');
            $table->index('organisation_id');
            $table->index(['type_id', 'status']);
            $table->index(['folder_id', 'is_current_version']);
            $table->index(['organisation_id', 'status']);
            $table->index(['is_current_version', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_digital_documents');
    }
};
