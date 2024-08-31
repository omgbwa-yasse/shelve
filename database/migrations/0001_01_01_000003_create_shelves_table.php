<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        /*
            User Jobs
        */

        Schema::create('user_organisation', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->notNull();
            $table->bigInteger('organisation_id')->unsigned()->notNull();
            $table->timestamps();
            $table->primary(['user_id', 'organisation_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->bigInteger('role_id')->unsigned()->notNull();
            $table->bigInteger('user_id')->unsigned()->notNull();
            $table->timestamps();
            $table->primary(['role_id', 'user_id']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique()->notNull();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->bigInteger('role_id')->unsigned()->notNull();
            $table->bigInteger('permission_id')->unsigned()->notNull();
            $table->primary(['role_id', 'permission_id']);
            $table->timestamps();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique()->notNull();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        /*
            Suivi des transactions du system
        */

        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->text('crypt')->notNull(); // crypt = crypt  + crypt(precedent) en MD5
            $table->string('action')->notNull();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        /*
            Les localisations des archives
        */

        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('building_id')->nullable(false);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->primary(['id', 'building_id']);
            $table->timestamps();
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('floor_id')->nullable(false);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->primary(['id', 'floor_id']);
            $table->timestamps();
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('shelves', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->nullable(false);
            $table->longText('observation')->nullable();
            $table->float('face', 10)->nullable(false);
            $table->float('ear', 10)->nullable(false);
            $table->float('shelf', 10)->nullable(false);
            $table->float('shelf_length', 15)->nullable(false);
            $table->unsignedBigInteger('room_id')->nullable(false);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->primary(['id', 'room_id']);
            $table->timestamps();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('container_properties', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->float('width', 15)->nullable(false);
            $table->float('length', 15)->nullable(false);
            $table->float('depth', 15)->nullable(false);
            $table->unique('name');
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->timestamps();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable(false);
            $table->unsignedBigInteger('shelve_id')->nullable(false);
            $table->unsignedBigInteger('status_id')->nullable(false);
            $table->unsignedBigInteger('property_id')->nullable(false);
            $table->foreign('shelve_id')->references('id')->on('shelves')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('container_status')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('container_properties')->onDelete('cascade');
            $table->unique('code');
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->timestamps();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('container_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->text('description')->nullable();
            $table->unique('name');
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->timestamps();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        /*
            Transferring archives
        */

        Schema::create('slip_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('slips', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(true)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('officer_organisation_id')->nullable(false);
            $table->unsignedBigInteger('officer_id')->nullable(false);
            $table->unsignedBigInteger('user_organisation_id')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(true);
            $table->unsignedBigInteger('slip_status_id')->nullable(false);
            $table->boolean('is_received')->nullable(true)->default(false);
            $table->dateTime('received_date')->nullable();
            $table->boolean('is_approved')->nullable(true)->default(false);
            $table->dateTime('approved_date')->nullable(true);
            $table->timestamps();
            $table->foreign('officer_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('officer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('slip_status_id')->references('id')->on('slip_statuses')->onDelete('cascade');
        });

        Schema::create('slip_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slip_id')->nullable(false);
            $table->string('code', 10)->nullable(false);
            $table->text('name')->nullable(false);
            $table->string('date_format', 1)->nullable(false);
            $table->string('date_start', 10)->nullable(true);
            $table->string('date_end', 10)->nullable(true);
            $table->date('date_exact')->nullable(true);
            $table->text('content')->nullable(true);
            $table->unsignedBigInteger('level_id')->nullable(false);
            $table->float('width', 10)->nullable(true);
            $table->string('width_description', 100)->nullable(true);
            $table->unsignedBigInteger('support_id')->nullable(false);
            $table->unsignedBigInteger('activity_id')->nullable(false);
            $table->unsignedBigInteger('container_id')->nullable(true);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->timestamps();
            $table->foreign('slip_id')->references('id')->on('slips')->onDelete('cascade');
            $table->foreign('support_id')->references('id')->on('record_supports')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        // A ajouter
        Schema::create('slip_record_attachment', function (Blueprint $table) {
            $table->unsignedBigInteger('slip_record_id')->nullable(false);
            $table->unsignedBigInteger('attachment_id')->nullable(false);
            $table->timestamps();
            $table->primary(['slip_record_id', 'attachment_id']);
            $table->foreign('slip_record_id')->references('id')->on('slip_records')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
        });

        Schema::create('slip_attachment', function (Blueprint $table) {
            $table->unsignedBigInteger('slip_id')->nullable(false);
            $table->unsignedBigInteger('attachment_id')->nullable(false);
            $table->timestamps();
            $table->primary(['slip_id', 'attachment_id']);
            $table->foreign('slip_record_id')->references('id')->on('slips')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
        });


        /*
            Modules taches
        */

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 70)->unique()->nullable(false);
            $table->text('description')->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->timestamps();
        });

        Schema::create('task_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 70)->unique()->nullable(false);
            $table->text('description')->nullable(false);
            $table->unsignedBigInteger('activity_id')->nullable(false);
            $table->timestamps();
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
        });

        Schema::create('task_statues', function (Blueprint $table) {
            $table->id();
            $table->string('name', 70)->unique()->nullable(false);
            $table->text('description')->nullable(false);
            $table->timestamps();
        });

        // Affectation des tâches aux intervenants

        Schema::create('task_organisations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->nullable(false);
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        Schema::create('task_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });


        // Association des taches aux ressources

        Schema::create('task_mail', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->timestamps();
            $table->primary(['task_id', 'mail_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
        });

        Schema::create('task_container', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable(false);
            $table->unsignedBigInteger('container_id')->nullable(false);
            $table->timestamps();
            $table->primary(['task_id', 'container_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
        });

        Schema::create('task_record', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable(false);
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->timestamps();
            $table->primary(['task_id', 'record_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
        });

        Schema::create('task_attachment', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable(false);
            $table->unsignedBigInteger('attachment_id')->nullable(false);
            $table->timestamps();
            $table->primary(['task_id', 'attachment_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
        });


        // Rappels

        Schema::create('task_remember', function (Blueprint $table) {
            $table->date('date_fix')->nullable();
            $table->enum('periode', ['before', 'after'])->nullable();
            $table->enum('date_trigger', ['start', 'end'])->nullable();
            $table->integer('limit_number')->nullable();
            $table->date('limit_date')->nullable();
            $table->integer('frequence_value')->unsigned()->nullable(false);
            $table->enum('frequence_unit', ['year', 'month', 'day', 'hour'])->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->unsignedBigInteger('task_id')->nullable(false);
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });



        // Supervision des activités

        Schema::create('task_supervision', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->boolean('task_assignation')->nullable();
            $table->boolean('task_update')->nullable();
            $table->boolean('task_parent_update')->nullable();
            $table->boolean('task_child_update')->nullable();
            $table->boolean('task_close')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });














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
            $table->unsignedBigInteger('accession_id')->nullable(true); // Versement
            $table->unsignedBigInteger('user_id')->nullable(false); // créateur
            $table->foreign('status_id')->references('id')->on('record_statuses')->onDelete('cascade');
            $table->foreign('support_id')->references('id')->on('record_supports')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            $table->foreign('accession_id')->references('id')->on('accessions')->onDelete('cascade');
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
            $table->timestamps();
        });

        /*
            Communication
        */

        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->nullable(false);
            $table->unsignedBigInteger('operator_id')->nullable(false);
            $table->unsignedBigInteger('operator_organisation_id')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->unsignedBigInteger('user_organisation_id')->nullable(false);
            $table->date('return_date')->nullable(false);
            $table->date('return_effective')->nullable();
            $table->unsignedBigInteger('status_id')->nullable(false);
            $table->timestamps();
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('communication_statuses')->onDelete('cascade');
            $table->foreign('user_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('operator_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        Schema::create('communication_record', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_id')->nullable(false);
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->boolean('is_original')->default(false)->nullable(false);
            $table->date('return_date')->nullable(false);
            $table->date('return_effective')->nullable();
            $table->unsignedBigInteger('operator_id')->nullable(false);
            $table->timestamps();
            $table->foreign('communication_id')->references('id')->on('communications')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('communication_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->nullable(false);
            $table->text('description')->nullable(true);
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->nullable(false);
            $table->unsignedBigInteger('operator_id')->nullable(false);
            $table->unsignedBigInteger('operator_organisation_id')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->unsignedBigInteger('user_organisation_id')->nullable(false);
            $table->unsignedBigInteger('status_id')->nullable(false);
            $table->timestamps();
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('reservation_statuses')->onDelete('cascade');
            $table->foreign('user_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('operator_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        Schema::create('reservation_record', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id')->nullable(false);
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->boolean('is_original')->default(false)->nullable(false);
            $table->date('reservation_date')->nullable(false);
            $table->unsignedBigInteger('operator_id')->nullable(false);
            $table->date('communication_id')->nullable();
            $table->timestamps();
            $table->foreign('communication_id')->references('id')->on('communications')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('reservation_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->nullable(false);
            $table->text('description')->nullable(true);
            $table->timestamps();
        });

        /*
            Les Outils de gestions
        */

        Schema::create('communicabilities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false)->unique(true);
            $table->string('name', 100)->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->text('description')->nullable(true);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false)->unique();
            $table->string('name', 100)->nullable(false);
            $table->text('observation')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('activities')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('activity_communicability', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_id')->nullable(false);
            $table->unsignedBigInteger('communicability_id')->nullable(false);
            $table->timestamps();
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('communicability_id')->references('id')->on('communicabilities')->onDelete('cascade');
        });

        Schema::create('retentions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->unsignedBigInteger('sort_id')->nullable(false);
            $table->timestamps();
            $table->foreign('sort_id')->references('id')->on('sorts')->onDelete('cascade');
        });

        Schema::create('sorts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 45)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('organisations')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('organisation_activity', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->unsignedBigInteger('activity_id')->nullable(false);
            $table->primary(['organisation_id', 'activity_id']);
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
        });

        Schema::create('retention_activity', function (Blueprint $table) {
            $table->unsignedBigInteger('retention_id')->nullable(false);
            $table->unsignedBigInteger('activity_id')->nullable(false);
            $table->primary(['retention_id', 'activity_id']);
            $table->foreign('retention_id')->references('id')->on('retentions')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
        });

        /*
            Thésaurus
        */

        Schema::create('term_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('name', 50);
            $table->string('native_name', 50)->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('language_id')->nullable(false);
            $table->unsignedBigInteger('category_id')->nullable(false);
            $table->unsignedBigInteger('type_id')->nullable(false);
            $table->unsignedBigInteger('parent_id')->nullable(false);
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('term_categories')->onDelete('set null');
            $table->foreign('type_id')->references('id')->on('term_typologies')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('terms')->onDelete('set null');
        });

        Schema::create('term_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('term_translations', function (Blueprint $table) {
            $table->unsignedBigInteger('term1_id')->nullable(false);
            $table->unsignedBigInteger('term1_language_id')->nullable(false);
            $table->unsignedBigInteger('term2_id')->nullable(false);
            $table->unsignedBigInteger('term2_language_id')->nullable(false);
            $table->primary(['term1_id', 'term2_id']);
            $table->foreign('term1_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('term1_language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('term2_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('term2_language_id')->references('id')->on('languages')->onDelete('cascade');
        });

        Schema::create('term_equivalent_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('term_equivalent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->string('term_used', 100)->nullable(false);
            $table->unsignedBigInteger('equivalent_type_id')->nullable(false);
            $table->timestamps();
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('equivalent_type_id')->references('id')->on('term_equivalent_types')->onDelete('cascade');
        });

        Schema::create('term_related', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id')->nullable(false);
            $table->unsignedBigInteger('term_related_id')->nullable(false);
            $table->timestamps();
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('term_related_id')->references('id')->on('terms')->onDelete('cascade');
        });

        /*
            Les courriers
        */

        Schema::create('mails', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(false)->unique();
            $table->string('name', 255)->nullable(false);
            $table->text('author')->nullable(false);
            $table->text('contacts')->nullable(false);
            $table->text('description')->nullable(true);
            $table->date('date')->nullable(false);
            $table->boolean('is_archived')->nullable(false);
            $table->unsignedBigInteger('subject_id')->nullable(false);
            $table->unsignedBigInteger('create_by')->nullable(false);
            $table->unsignedBigInteger('update_by')->nullable(true);
            $table->unsignedBigInteger('mail_priority_id')->nullable(false);
            $table->unsignedBigInteger('mail_type_id')->nullable(false);
            $table->unsignedBigInteger('mail_typology_id')->nullable(false);
            $table->unsignedBigInteger('document_type_id')->nullable(false);
            $table->timestamps();
            $table->foreign('subject_id')->references('id')->on('mail_subjects')->onDelete('cascade');
            $table->foreign('create_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('update_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mail_priority_id')->references('id')->on('mail_priorities')->onDelete('cascade');
            $table->foreign('mail_type_id')->references('id')->on('mail_types')->onDelete('cascade');
            $table->foreign('mail_typology_id')->references('id')->on('mail_typologies')->onDelete('cascade');
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
        });

        Schema::create('mail_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->boolean('to_return')->nullable(true);
            $table->text('description')->nullable(false);
            $table->timestamps();
        });

        Schema::create('mail_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(false)->unique();
            $table->dateTime('date_creation')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('user_send_id')->nullable(false);
            $table->unsignedBigInteger('organisation_send_id')->nullable(false);
            $table->unsignedBigInteger('user_received_id')->nullable();
            $table->unsignedBigInteger('organisation_received_id')->nullable();
            $table->unsignedBigInteger('mail_type_id')->nullable(false);
            $table->unsignedBigInteger('document_type_id')->nullable(false);
            $table->unsignedBigInteger('action_id')->nullable(false);
            $table->text('description')->nullable(false);
            $table->timestamps();
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('user_send_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organisation_send_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('user_received_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('organisation_received_id')->references('id')->on('organisations')->onDelete('set null');
            $table->foreign('mail_type_id')->references('id')->on('mail_types')->onDelete('cascade');
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
            $table->foreign('action_id')->references('id')->on('mail_actions')->onDelete('cascade');
        });

        Schema::create('mail_organisation', function (Blueprint $table) {
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->boolean('is_original')->nullable(false);
            $table->primary(['mail_id', 'organisation_id']);
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        Schema::create('mail_types', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['send', 'received','InProgress'])->nullable(false);
            $table->timestamps();
        });

        Schema::create('mail_archiving', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('container_id')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('document_type_id')->nullable(false);
            $table->timestamps();
            $table->foreign('container_id')->references('id')->on('mail_containers')->onDelete('cascade');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
        });

        Schema::create('mail_containers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable(false);
            $table->string('name', 100)->nullable();
            $table->unsignedBigInteger('type_id')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->foreign('type_id')->references('id')->on('container_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('mail_status', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
        });

        Schema::create('mail_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('duration');
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('path', 100)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->string('crypt', 255)->nullable(false);
            $table->integer('size')->nullable(false);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->timestamps();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('mail_attachment', function (Blueprint $table) {
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('attachment_id')->nullable(false);
            $table->primary(['mail_id', 'attachment_id']);
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
        });

        Schema::create('mail_typologies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->unsignedBigInteger('class_id')->nullable(false);
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
        });

        Schema::create('mail_author', function (Blueprint $table) {
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('mail_id');
            $table->primary(['author_id', 'mail_id']);
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
        });

        /*
            Propritées communes
        */

        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false)->unique();
            $table->longText('description')->nullable(false);
            $table->timestamps();
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('type_id')->nullable(false);
            $table->string('name', 100)->nullable(false)->unique();
            $table->string('parallel_name', 100)->nullable(true);
            $table->string('other_name', 100)->nullable(true);
            $table->string('lifespan', 100)->nullable(true);
            $table->string('locations', 100)->nullable(true);
            $table->unsignedInteger('parent_id')->nullable(true);
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('type_id')->references('id')->on('author_types')->onDelete('cascade');
        });

        Schema::create('author_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false)->unique();
            $table->longText('description')->nullable(false);
            $table->timestamps();
        });

        Schema::create('container_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
        });

        Schema::create('author_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('type_id')->nullable(false);
            $table->string('name', 100)->nullable(false)->unique();
            $table->string('parallel_name', 100)->nullable(true);
            $table->string('other_name', 100)->nullable(true);
            $table->string('lifespan', 100)->nullable(true);
            $table->string('locations', 100)->nullable(true);
            $table->unsignedInteger('parent_id')->nullable(true);
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('type_id')->references('id')->on('author_types')->onDelete('cascade');
        });

        Schema::create('author_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('author_id')->nullable(true);
            $table->string('phone1')->nullable();
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->string('fax')->nullable();
            $table->text('other')->nullable();
            $table->string('po_box')->nullable();
            $table->timestamps();
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('set null');
        });

        /*
            Les parapheurs
        */

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false)->unique();
            $table->string('name', 250)->nullable(false);
            $table->unsignedInteger('organisation_holder_id')->nullable(false);
            $table->foreign('organisation_holder_id')->references('id')->on('batches')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('batch_mail', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('batch_id')->nullable(true);
            $table->unsignedBigInteger('mail_id')->nullable(true);
            $table->dateTime('insert_date')->nullable(true);
            $table->dateTime('remove_date')->nullable(true);
            $table->timestamps();
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
        });

        Schema::create('batch_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('batch_id')->nullable(false);
            $table->unsignedBigInteger('organisation_send_id')->nullable(false);
            $table->unsignedBigInteger('organisation_received_id')->nullable(false);
            $table->timestamps();
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('organisation_send_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('organisation_received_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        /*
            Les chariots
        */

        Schema::create('dolly_mails', function(Blueprint $table){
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('dolly_records', function(Blueprint $table){
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('dolly_slips', function(Blueprint $table){
            $table->unsignedBigInteger('slip_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('slip_id')->references('id')->on('slips')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('dolly_slip_records', function(Blueprint $table){
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->unsignedBigInteger('slip_id')->nullable(false);
            $table->foreign('slip_id')->references('id')->on('slips')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('dolly_buildings', function(Blueprint $table){
            $table->unsignedBigInteger('building_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('dolly_shelves', function(Blueprint $table){
            $table->unsignedBigInteger('shelf_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('shelf_id')->references('id')->on('shelves')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('dolly_containers', function(Blueprint $table){
            $table->unsignedBigInteger('container_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('dolly_communications', function(Blueprint $table){
            $table->unsignedBigInteger('communication_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('communication_id')->references('id')->on('communications')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('dolly_rooms', function(Blueprint $table){
            $table->unsignedBigInteger('room_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('dollies', function(Blueprint $table){
            $table->id();
            $table->string('name', 70)->unique(true)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('type_id')->nullable(false);
            $table->foreign('type_id')->references('id')->on('dolly_types')->onDelete('cascade');
        });

        Schema::create('dolly_types', function(Blueprint $table){
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dolly_types');
        Schema::dropIfExists('dollies');
        Schema::dropIfExists('dolly_mails');
        Schema::dropIfExists('dolly_records');
        Schema::dropIfExists('dolly_slips');
        Schema::dropIfExists('dolly_slip_records');
        Schema::dropIfExists('dolly_buildings');
        Schema::dropIfExists('dolly_shelves');
        Schema::dropIfExists('dolly_containers');
        Schema::dropIfExists('dolly_communications');
        Schema::dropIfExists('dolly_rooms');
        Schema::dropIfExists('batch_transactions');
        Schema::dropIfExists('batch_mail');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('author_contacts');
        Schema::dropIfExists('author_addresses');
        Schema::dropIfExists('container_types');
        Schema::dropIfExists('author_types');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('document_types');
        Schema::dropIfExists('mail_author');
        Schema::dropIfExists('mail_typologies');
        Schema::dropIfExists('mail_attachment');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('mail_priorities');
        Schema::dropIfExists('mail_status');
        Schema::dropIfExists('mail_containers');
        Schema::dropIfExists('mail_archiving');
        Schema::dropIfExists('mail_types');
        Schema::dropIfExists('mail_organisation');
        Schema::dropIfExists('mail_transactions');
        Schema::dropIfExists('mails');
        Schema::dropIfExists('term_related');
        Schema::dropIfExists('term_equivalent');
        Schema::dropIfExists('term_equivalent_types');
        Schema::dropIfExists('term_translations');
        Schema::dropIfExists('term_types');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('term_categories');
        Schema::dropIfExists('retention_activity');
        Schema::dropIfExists('organisation_activity');
        Schema::dropIfExists('organisations');
        Schema::dropIfExists('sorts');
        Schema::dropIfExists('retentions');
        Schema::dropIfExists('activity_communicability');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('communicabilities');
        Schema::dropIfExists('communications');
        Schema::dropIfExists('files');
        Schema::dropIfExists('record_links');
        Schema::dropIfExists('record_term');
        Schema::dropIfExists('record_keyword');
        Schema::dropIfExists('record_levels');
        Schema::dropIfExists('record_supports');
        Schema::dropIfExists('record_status');
        Schema::dropIfExists('record_author');
        Schema::dropIfExists('records');
        Schema::dropIfExists('accessions');
        Schema::dropIfExists('accession_status');
        Schema::dropIfExists('container_statuses');
        Schema::dropIfExists('containers');
        Schema::dropIfExists('container_properties');
        Schema::dropIfExists('shelves');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('floors');
        Schema::dropIfExists('buildings');
    }
};
