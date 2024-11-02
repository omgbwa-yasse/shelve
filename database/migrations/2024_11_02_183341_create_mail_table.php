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
            $table->string('name', 50);
            $table->string('duration');
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
            $table->string('name', 50);
            $table->string('description', 100)->nullable();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('mail_containers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('name', 100)->nullable();
            $table->foreignId('type_id')->constrained('container_types')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_organisation_id')->constrained('organisations')->cascadeOnDelete();
            $table->timestamps();
        });



        // Table principale des mails
        Schema::create('mails', function (Blueprint $table) {
            $table->id();

            // Informations de base
            $table->string('code')->unique();
            $table->string('subject', 255);
            $table->string('author');
            $table->text('description')->nullable();
            $table->date('date');

            // Type de transaction
            $table->enum('type', ['outbound', 'inbound'])->default('inbound');

            // Relations
            $table->foreignId('mail_priority_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mail_typology_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();

            // Relations utilisateurs et organisations
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sender_organisation_id')->constrained('organisations')->cascadeOnDelete();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_organisation_id')->nullable()->constrained('organisations')->nullOnDelete();

            // Action et statut
            $table->foreignId('action_id')->constrained('mail_actions')->cascadeOnDelete();
            $table->boolean('is_archived')->default(false);

            $table->timestamps();
        });





        // Tables pivots
        Schema::create('mail_related', function (Blueprint $table) {
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mail_related_id')->constrained('mails')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['mail_id', 'mail_related_id']);
        });




        Schema::create('mail_organisation', function (Blueprint $table) {
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_original');

            $table->primary(['mail_id', 'organisation_id']);
            $table->timestamps();
        });



        Schema::create('mail_archiving', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained('mail_containers')->cascadeOnDelete();
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });



        Schema::create('mail_attachment', function (Blueprint $table) {
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attachment_id')->constrained('attachments')->cascadeOnDelete();

            $table->primary(['mail_id', 'attachment_id']);
            $table->timestamps();
        });



        Schema::create('mail_author', function (Blueprint $table) {
            $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
            $table->foreignId('mail_id')->constrained()->cascadeOnDelete();

            $table->primary(['author_id', 'mail_id']);
            $table->timestamps();
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
        Schema::dropIfExists('mail_organisation');
        Schema::dropIfExists('mail_related');
        Schema::dropIfExists('mails');
        Schema::dropIfExists('mail_containers');
        Schema::dropIfExists('mail_typologies');
        Schema::dropIfExists('mail_actions');
        Schema::dropIfExists('mail_priorities');
    }
};
