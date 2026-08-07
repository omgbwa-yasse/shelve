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
        /*
            Les enregistrements
        */

        Schema::create('records', function (Blueprint $table) {
            $table->id();
            // Zone d'identification
            $table->string('code', 10)->nullable(false); // Référence
            $table->text('name')->nullable(false); // intitulé et analyse
            $table->string('date_format', 1)->nullable(false); // format de date
            $table->string('date_start', 10)->nullable(true); // date de début
            $table->string('date_end', 10)->nullable(true); // date de fin
            $table->date('date_exact')->nullable(true); // date exacte
            $table->unsignedBigInteger('level_id')->nullable(false); // Niveau de description
            $table->float('width', 10)->nullable(true); // Epaisseur en cm
            $table->string('width_description', 100)->nullable(true); // Importance matérielle

            // zone du contexte
            $table->text('biographical_history')->nullable(true); // histoire administrative
            $table->text('archival_history')->nullable(true); // Historique de conservation
            $table->text('acquisition_source')->nullable(true); // Modalités d'entrée

            // zone du contenu et structure
            $table->text('content')->nullable(true); // Présentation du contenu
            $table->text('appraisal')->nullable(true); // Evaluation, tri et élimination, sort final
            $table->text('accrual')->nullable(true); // Accroissements
            $table->text('arrangement')->nullable(true); // Mode de classement

            // zone du condition d'accès et utilisation
            $table->string('access_conditions', 50)->nullable(true); // Conditions d'accès
            $table->string('reproduction_conditions', 50)->nullable(true); // Conditions de reproduction
            $table->string('language_material', 50)->nullable(true); // Langue et écriture des documents
            $table->string('characteristic', 100)->nullable(true); // Caractériqtiques matérielles et contraintes techniques
            $table->string('finding_aids', 100)->nullable(true); // Instrument de recherche

            // zone du source complémentaires
            $table->string('location_original', 100)->nullable(true); // Existence et lieu de conservation des originaux
            $table->string('location_copy', 100)->nullable(true); // Existence et leiu de conservation de copies
            $table->string('related_unit', 100)->nullable(true); //  Sources complémentaires
            $table->text('publication_note')->nullable(true); // Bibliographie

            // zone de note
            $table->text('note')->nullable(true); // Notes

            // zone de control area
            $table->text('archivist_note')->nullable(true); // Note de l'archiviste
            $table->string('rule_convention', 100)->nullable(true); // Règles ou conventions
            $table->timestamps();

            // clés étarnagères
            $table->unsignedBigInteger('status_id')->nullable(false); // Status de unité de description
            $table->unsignedBigInteger('support_id')->nullable(false); // Support
            $table->unsignedBigInteger('activity_id')->nullable(false); // Activité rattachée
            $table->unsignedBigInteger('parent_id')->nullable(true); // Fiche de description parente
            $table->unsignedBigInteger('container_id')->nullable(true); // Lieu de consersation
            $table->unsignedBigInteger('user_id')->nullable(false); // créateur
            $table->foreign('status_id')->references('id')->on('record_statuses')->onDelete('cascade');
            $table->foreign('support_id')->references('id')->on('record_supports')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('record_author', function (Blueprint $table) {
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('record_id');
            $table->primary(['author_id', 'record_id']);
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
        });

        Schema::create('record_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('record_supports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('record_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('child_id')->nullable();
            $table->boolean('has_child')->default(true);
            $table->foreign('child_id')->references('id')->on('record_levels')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('record_keyword', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('keyword_id')->nullable(false);
            $table->primary(['record_id', 'keyword_id']);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('keywords')->onDelete('cascade');
        });

        Schema::create('record_term', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedInteger('term_id')->nullable(false);
            $table->timestamps();
            $table->primary(['record_id', 'term_id']);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
        });

        Schema::create('record_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('parent_id')->nullable(false);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('records')->onDelete('cascade');
        });

        Schema::create('record_attachment', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('attachment_id')->nullable(false);
            $table->timestamps();
            $table->primary(['record_id', 'attachment_id']);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
        });

        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('path', 250)->nullable(false);
            $table->string('crypt', 250)->nullable(false);
            $table->string('size', 45)->nullable();
            $table->string('extension', 10)->nullable(false);
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
        });

        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('name', 250)->nullable(false)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });


        Schema::create('record_container', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id');
            $table->unsignedInteger('container_id');
            $table->string('description', 100)->nullable();
            $table->unsignedBigInteger('creator_id');
            $table->timestamps();
            $table->primary(['record_id', 'container_id']);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record');
    }
};
