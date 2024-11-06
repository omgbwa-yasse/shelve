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
        // Créer d'abord les tables sans dépendances
        Schema::create('mail_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique(); // Assurer l'unicité des noms de priorités
            $table->integer('duration');
            $table->timestamps();
        });

        Schema::create('mail_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->integer('duration');
            $table->boolean('to_return')->default(false);
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('mail_typologies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();  // Assurer l'unicité des noms de typologies
            $table->text('description')->nullable();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('mail_containers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();  // Assurer l'unicité des codes de conteneurs
            $table->string('name', 100)->nullable();
            $table->foreignId('type_id')->constrained('container_types')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('creator_organisation_id')->constrained('organisations')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('mails', function (Blueprint $table) {
            $table->id();
            $table->string('code', 25)->unique(true);
            $table->string('name', 150);
            $table->datetime('date');
            $table->text('description')->nullable();
            $table->enum('document_type', ['original', 'duplicate', 'copy'])->default('original');
            $table->enum('status', ['draft', 'in_progress', 'transmitted', 'reject'])->default('draft'); // Correction orthographique
            $table->foreignId('priority_id')->constrained('mail_priorities')->cascadeOnDelete();
            $table->foreignId('typology_id')->constrained('mail_typologies')->cascadeOnDelete();
            $table->foreignId('action_id')->constrained('mail_actions')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sender_organisation_id')->constrained('organisations')->cascadeOnDelete();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        // Tables pivots
        Schema::create('mail_related', function (Blueprint $table) {
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mail_related_id')->constrained('mails')->cascadeOnDelete();
            $table->primary(['mail_id', 'mail_related_id']);
            $table->timestamps(); // Timestamps pour la table pivot
        });

        Schema::create('mail_archives', function (Blueprint $table) { // Nom de table corrigé
            $table->id();
            $table->foreignId('container_id')->constrained('mail_containers')->cascadeOnDelete();
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('archived_by')->constrained('users')->cascadeOnDelete(); // Nom de colonne plus clair
            $table->enum('document_type', ['original', 'duplicate', 'copy'])->default('original');
            $table->timestamps();
        });

        Schema::create('mail_attachment', function (Blueprint $table) {
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attachment_id')->constrained('attachments')->cascadeOnDelete();
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete(); // Nom de colonne plus clair
            $table->primary(['mail_id', 'attachment_id']);
            $table->timestamps(); // Timestamps pour la table pivot
        });

        Schema::create('mail_author', function (Blueprint $table) {
            $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
            $table->foreignId('mail_id')->constrained('mails')->cascadeOnDelete();
            $table->primary(['author_id', 'mail_id']);
            $table->timestamps(); // Timestamps pour la table pivot
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les tables dans l'ordre inverse de leur création
        Schema::dropIfExists('mail_author');
        Schema::dropIfExists('mail_attachment');
        Schema::dropIfExists('mail_archiving');
        Schema::dropIfExists('mail_related');
        Schema::dropIfExists('mails');
        Schema::dropIfExists('mail_containers');
        Schema::dropIfExists('mail_typologies');
        Schema::dropIfExists('mail_actions');
        Schema::dropIfExists('mail_priorities');
    }
};
