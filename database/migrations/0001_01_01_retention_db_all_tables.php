<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(){

        Schema::create('communicabilities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->integer('time')->nullable(false);
            $table->text('reference')->nullable(false);
            $table->timestamps();
        });

        Schema::create('classifications', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false)->unique();
            $table->string('name', 100)->nullable(false);
            $table->unsignedBigInteger('parent_id')->nullable(false);
            $table->text('observation')->nullable();
            $table->unsignedBigInteger('communicability_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('classifications')->onDelete('cascade');
            $table->foreign('communicability_id')->references('id')->on('communicabilities')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('dolly_records', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->timestamps();
        });


        Schema::create('access_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 10)->nullable(false);
            $table->text('description')->nullable(false);
            $table->unsignedBigInteger('classification_id')->nullable(false);
            $table->primary(['id', 'classification_id']);
            $table->foreign('classification_id')->references('id')->on('classifications')->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('name', 250)->nullable(false)->unique();
            $table->timestamps();
        });

        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->primary('id');
            $table->foreign('parent_id')->references('id')->on('organisations')->onDelete('set null');
        });

        Schema::create('record_status', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('observation', 250)->nullable();
            $table->primary('id');
        });


        Schema::create('record_supports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('observation', 250)->nullable();
            $table->primary('id');
        });

        Schema::create('record_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->tinyInteger('child')->nullable(false);
            $table->primary('id');
        });

        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->primary('id');
        });

        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('building_id')->nullable(false);
            $table->primary(['id', 'building_id']);
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
        });


        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 10)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('floor_id')->nullable(false);
            $table->primary(['id', 'floor_id']);
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');
        });

        Schema::create('shelves', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->nullable(false);
            $table->longText('observation')->nullable();
            $table->string('ear', 10)->nullable(false);
            $table->string('face', 10)->nullable(false);
            $table->string('colonne', 10)->nullable(false);
            $table->string('table', 10)->nullable(false);
            $table->unsignedBigInteger('room_id')->nullable(false);
            $table->primary('id');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });

        Schema::create('container_status', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->nullable(false);
            $table->text('description')->nullable();
            $table->primary('id');
            $table->unique('name');
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->float('width', 15, 6)->nullable(false);
            $table->float('lengh', 15, 6)->nullable(false);
            $table->float('thinkness', 15, 6)->nullable(false);
            $table->primary('id');
            $table->unique('name');
        });


        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->nullable(false);
            $table->unsignedBigInteger('shelve_id')->nullable(false);
            $table->unsignedBigInteger('status_id')->nullable(false);
            $table->unsignedBigInteger('property_id')->nullable(false);
            $table->primary('id');
            $table->foreign('shelve_id')->references('id')->on('shelves')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('container_status')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->unique('reference');
        });

        Schema::create('transfer_status', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->nullable(false);
            $table->text('observation')->nullable();
            $table->primary('id');
        });

        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->datetime('date_creation')->nullable(false);
            $table->datetime('date_authorize')->nullable();
            $table->text('observation')->nullable();
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->unsignedBigInteger('transfer_status_id')->nullable(false);
            $table->primary('id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('transfer_status_id')->references('id')->on('transfer_status')->onDelete('cascade');
        });


        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->nullable(false);
            $table->text('name')->nullable(false);
            $table->string('date_format', 1)->nullable(false);
            $table->string('date_start', 10)->nullable();
            $table->string('date_end', 10)->nullable();
            $table->date('date_exact')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('level_id')->nullable(false);
            $table->unsignedBigInteger('status_id')->nullable(false);
            $table->unsignedBigInteger('support_id')->nullable(false);
            $table->unsignedBigInteger('classification_id')->nullable(false);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable(false);
            $table->unsignedBigInteger('transfer_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->primary('id');
            $table->foreign('status_id')->references('id')->on('record_status')->onDelete('cascade');
            $table->foreign('support_id')->references('id')->on('record_supports')->onDelete('cascade');
            $table->foreign('classification_id')->references('id')->on('classifications')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            $table->foreign('transfer_id')->references('id')->on('transfers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('retention_sorts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 45)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->primary('id');
        });

        Schema::create('retentions', function (Blueprint $table) {
            $table->id();
            $table->integer('duration')->nullable(false);
            $table->integer('sort')->nullable(false);
            $table->text('reference')->nullable(false);
            $table->unsignedBigInteger('retention_sort_id')->nullable(false);
            $table->primary('id');
            $table->foreign('retention_sort_id')->references('id')->on('retention_sorts')->onDelete('cascade');
        });


        Schema::create('organisation_classification', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->unsignedBigInteger('classification_id')->nullable(false);
            $table->primary(['organisation_id', 'classification_id']);
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('classification_id')->references('id')->on('classifications')->onDelete('cascade');
        });

        Schema::create('retention_classification', function (Blueprint $table) {
            $table->unsignedBigInteger('retention_id')->nullable(false);
            $table->unsignedBigInteger('classification_id')->nullable(false);
            $table->primary(['retention_id', 'classification_id']);
            $table->foreign('retention_id')->references('id')->on('retentions')->onDelete('cascade');
            $table->foreign('classification_id')->references('id')->on('classifications')->onDelete('cascade');
        });

        Schema::create('record_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('parent_id')->nullable(false);
            $table->primary('id');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('records')->onDelete('cascade');
        });

        Schema::create('record_keyword', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('keyword_id')->nullable(false);
            $table->primary(['record_id', 'keyword_id']);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('keywords')->onDelete('cascade');
        });

        Schema::create('record_documents', function (Blueprint $table) {
            $table->id();
            $table->string('path', 250)->nullable(false);
            $table->string('crypt', 250)->nullable(false);
            $table->string('size', 45)->nullable();
            $table->string('extension', 10)->nullable(false);
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->primary('id');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
        });

        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('operator')->nullable(false);
            $table->unsignedBigInteger('user')->nullable(false);
            $table->datetime('date_creation')->nullable(false);
            $table->datetime('return')->nullable(false);
            $table->datetime('return_effective')->nullable();
            $table->primary('id');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('operator')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('mail_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->primary('id');
            $table->unique('mail_priority_name');
        });

        Schema::create('mail_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('path', 45)->nullable(false);
            $table->string('filename', 45)->nullable(false);
            $table->string('crypt', 255)->nullable(false);
            $table->string('size', 10)->nullable(false);
            $table->primary('id');
        });

        Schema::create('typology_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 10)->nullable(false);
            $table->unsignedInteger('parent_id')->nullable();
            $table->primary('id');
        });

        Schema::create('mail_typologies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->unsignedBigInteger('typology_category_id')->nullable(false);
            $table->primary('id');
            $table->foreign('typology_category_id')->references('id')->on('typology_categories')->onDelete('cascade');
        });

        Schema::create('mails', function (Blueprint $table) {
            $table->id();
            $table->integer('reference')->nullable(false);
            $table->string('object', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->string('authors', 100)->nullable(false);
            $table->datetime('create_at')->nullable(false);
            $table->datetime('update_at')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->unsignedBigInteger('mail_priority_id')->nullable(false);
            $table->unsignedBigInteger('mail_typology_id')->nullable(false);
            $table->primary('id');
            $table->foreign('mail_priority_id')->references('mail_priority_id')->on('mail_priorities')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('mail_attachments')->onDelete('cascade');
            $table->foreign('mail_typology_id')->references('iid')->on('mail_typologies')->onDelete('cascade');
        });

        Schema::create('container_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->primary('id');
        });

        Schema::create('mail_containers', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->nullable(false);
            $table->string('name', 50)->nullable();
            $table->unsignedBigInteger('type_id')->nullable(false);
            $table->primary('id');
            $table->foreign('type_id')->references('id')->on('container_types')->onDelete('cascade');
        });

        Schema::create('mail_status', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->primary('id');
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('reference')->nullable(false);
            $table->datetime('date_creation')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('user_send')->nullable(false);
            $table->unsignedBigInteger('organisation_send_id')->nullable(false);
            $table->unsignedBigInteger('user_receveid')->nullable();
            $table->unsignedBigInteger('organisation_received_id')->nullable();
            $table->integer('mail_status_id')->nullable(false);
            $table->datetime('create_at')->nullable(false);
            $table->datetime('update_at')->nullable();
            $table->primary('id');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('user_send')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organisation_send_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('user_receveid')->references('id')->on('users')->onDelete('set null');
            $table->foreign('organisation_received_id')->references('id')->on('organisations')->onDelete('set null');
            $table->foreign('mail_status_id')->references('id')->on('mail_status')->onDelete('cascade');
        });

        Schema::create('mail_container', function (Blueprint $table) {
            $table->unsignedBigInteger('container_id')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->primary(['container_id', 'mail_id']);
            $table->foreign('container_id')->references('id')->on('mail_containers')->onDelete('cascade');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
        });

        Schema::create('dolly_loans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->primary('id');
            $table->unique('name');
        });

        Schema::create('office', function (Blueprint $table) {
            $table->id();
            $table->string('poste', 100)->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->primary('id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        Schema::create('user_office', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->unsignedInteger('office_id')->nullable(false);
            $table->primary(['user_id', 'office_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
        });

        Schema::create('mailbatchs', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 100)->nullable();
            $table->string('name', 100)->nullable(false);
            $table->unsignedInteger('type_id')->nullable(false);
            $table->primary('id');
        });

        Schema::create('mailbatch_transaction', function (Blueprint $table) {
            $table->unsignedInteger('mailbatch_id')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('organisation_send_id')->nullable(false);
            $table->unsignedBigInteger('organisation_received_id')->nullable(false);
            $table->datetime('create_at')->nullable(false);
            $table->datetime('update_at')->nullable(false);
            $table->primary(['mailbatch_id', 'mail_id', 'organisation_send_id', 'organisation_received_id']);
            $table->foreign('mailbatch_id')->references('id')->on('mailbatchs')->onDelete('cascade');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('organisation_send_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('organisation_received_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        Schema::create('term_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->primary('id');
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('term', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->string('Language', 100)->nullable();
            $table->integer('specificity_level')->nullable();
            $table->primary('id');
        });

        Schema::create('relation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->primary('id');
        });

        Schema::create('term_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('parent_id')->nullable(false);
            $table->unsignedInteger('child_id')->nullable(false);
            $table->unsignedInteger('category_id')->nullable(false);
            $table->unsignedInteger('relation_type_id')->nullable(false);
            $table->primary('id');
            $table->foreign('parent_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('term_categories')->onDelete('cascade');
            $table->foreign('relation_type_id')->references('id')->on('relation_types')->onDelete('cascade');
        });

        Schema::create('term_record', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedInteger('term_id')->nullable(false);
            $table->primary(['record_id', 'term_id']);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('term_record');
        Schema::dropIfExists('term_relations');
        Schema::dropIfExists('relation_types');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('floors');
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('record_levels');
        Schema::dropIfExists('record_supports');
        Schema::dropIfExists('record_status');
        Schema::dropIfExists('organisations');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('container_status');
        Schema::dropIfExists('shelves');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('transfer_status');
        Schema::dropIfExists('containers');
        Schema::dropIfExists('record_documents');
        Schema::dropIfExists('record_keyword');
        Schema::dropIfExists('record_links');
        Schema::dropIfExists('retention_classification');
        Schema::dropIfExists('organisation_classification');
        Schema::dropIfExists('retentions');
        Schema::dropIfExists('mail_typologies');
        Schema::dropIfExists('typology_categories');
        Schema::dropIfExists('mail_attachments');
        Schema::dropIfExists('mail_priorities');
        Schema::dropIfExists('communications');
        Schema::dropIfExists('mail_status');
        Schema::dropIfExists('mail_containers');
        Schema::dropIfExists('container_types');
        Schema::dropIfExists('mails');
        Schema::dropIfExists('dolly_loans');
        Schema::dropIfExists('mail_container');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('term_categories');
        Schema::dropIfExists('mailbatch_transaction');
        Schema::dropIfExists('mailbatchs');
        Schema::dropIfExists('user_office');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('retention_sorts');
        Schema::dropIfExists('records');
        Schema::dropIfExists('user_types');
        Schema::dropIfExists('keywords');
        Schema::dropIfExists('dolly_records');
        Schema::dropIfExists('access_classifications');
        Schema::dropIfExists('classifications');
        Schema::dropIfExists('communicabilities');
    }
};
