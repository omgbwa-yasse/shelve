<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(){


          /*


            User Jobs


        */



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



        /*


            Les localisations des archives



        */




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
            $table->string('code', 10)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('floor_id')->nullable(false);
            $table->primary(['id', 'floor_id']);
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');
        });



        Schema::create('shelves', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->nullable(false);
            $table->longText('observation')->nullable();
            $table->string('face', 10)->nullable(false);
            $table->string('ear', 10)->nullable(false);
            $table->string('shelf', 10)->nullable(false);
            $table->float('shelf_length', 15, 6)->nullable(false);
            $table->unsignedBigInteger('room_id')->nullable(false);
            $table->primary('id');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });



        Schema::create('container_properties', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->float('width', 15, 6)->nullable(false);
            $table->float('length', 15, 6)->nullable(false);
            $table->float('depth', 15, 6)->nullable(false);
            $table->primary('id');
            $table->unique('name');
        });


        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable(false);
            $table->unsignedBigInteger('shelve_id')->nullable(false);
            $table->unsignedBigInteger('status_id')->nullable(false);
            $table->unsignedBigInteger('property_id')->nullable(false);
            $table->primary('id');
            $table->foreign('shelve_id')->references('id')->on('shelves')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('container_status')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('container_properties')->onDelete('cascade');
            $table->unique('code');
        });


        Schema::create('container_status', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->nullable(false);
            $table->text('description')->nullable();
            $table->primary('id');
            $table->unique('name');
        });




        /*


            Les courriers



        */


        Schema::create('accession_status', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->nullable(false);
            $table->text('observation')->nullable();
            $table->primary('id');
        });



        Schema::create('accessions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->datetime('date_creation')->nullable(false);
            $table->datetime('date_authorize')->nullable();
            $table->text('observation')->nullable();
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->unsignedBigInteger('accession_status_id')->nullable(false);
            $table->primary('id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('accession_status_id')->references('id')->on('accession_status')->onDelete('cascade');
        });








        /*


            Les enregistrements



        */




        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable(false);
            $table->text('name')->nullable(false);
            $table->string('date_format', 1)->nullable(false);
            $table->string('date_start', 10)->nullable();
            $table->string('date_end', 10)->nullable();
            $table->date('date_exact')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('level_id')->nullable(false);
            $table->unsignedBigInteger('status_id')->nullable(false);
            $table->unsignedBigInteger('support_id')->nullable(false);
            $table->unsignedBigInteger('activity_id')->nullable(false);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable(false);
            $table->unsignedBigInteger('accession_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->primary('id');
            $table->foreign('status_id')->references('id')->on('record_status')->onDelete('cascade');
            $table->foreign('support_id')->references('id')->on('record_supports')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            $table->foreign('accession_id')->references('id')->on('accessions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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


        Schema::create('record_keyword', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('keyword_id')->nullable(false);
            $table->primary(['record_id', 'keyword_id']);
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('keywords')->onDelete('cascade');
        });


        Schema::create('record_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('parent_id')->nullable(false);
            $table->primary('id');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('records')->onDelete('cascade');
        });



        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('path', 250)->nullable(false);
            $table->string('crypt', 250)->nullable(false);
            $table->string('size', 45)->nullable();
            $table->string('extension', 10)->nullable(false);
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->primary('id');
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
            $table->unsignedBigInteger('record_id')->nullable(false);
            $table->unsignedBigInteger('operator')->nullable(false);
            $table->unsignedBigInteger('user')->nullable(false);
            $table->timestamps();
            $table->datetime('return')->nullable(false);
            $table->datetime('return_effective')->nullable();
            $table->primary('id');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('operator')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');
        });






        /*


            Les Outils de gestions



        */






        Schema::create('communicabilities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->text('code')->nullable(false);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false)->unique();
            $table->string('name', 100)->nullable(false);
            $table->text('observation')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('communicability_id')->nullable(false);
            $table->foreign('parent_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('communicability_id')->references('id')->on('communicabilities')->onDelete('set null');
            $table->timestamps();
        });




        Schema::create('retentions', function (Blueprint $table) {
            $table->id();
            $table->integer('duration')->nullable(false);
            $table->integer('sort')->nullable(false);
            $table->text('code')->nullable(false);
            $table->unsignedBigInteger('sort_id')->nullable(false);
            $table->primary('id');
            $table->foreign('sort_id')->references('id')->on('sorts')->onDelete('cascade');
        });


        Schema::create('sorts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 45)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->primary('id');
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


        Schema::create('access_activities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 10)->nullable(false);
            $table->text('description')->nullable(false);
            $table->unsignedBigInteger('activity_id')->nullable(false);
            $table->primary(['id', 'activity_id']);
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
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


            Les courriers



        */



        Schema::create('mails', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(false);
            $table->string('object', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->date('date')->nullable(false);
            $table->unsignedBigInteger('mail_priority_id')->nullable(false);
            $table->unsignedBigInteger('mail_type_id')->nullable(false);
            $table->unsignedBigInteger('mail_typology_id')->nullable(false);
            $table->timestamps();
            $table->foreign('mail_priority_id')->references('id')->on('mail_priorities')->onDelete('cascade');
            $table->foreign('mail_type_id')->references('id')->on('mail_types')->onDelete('cascade');
            $table->foreign('mail_typology_id')->references('id')->on('mail_typologies')->onDelete('cascade');
        });


        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->nullable(false);
            $table->datetime('date_creation')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('user_send')->nullable(false);
            $table->unsignedBigInteger('organisation_send_id')->nullable(false);
            $table->unsignedBigInteger('user_received')->nullable();
            $table->unsignedBigInteger('organisation_received_id')->nullable();
            $table->integer('mail_status_id')->nullable(false);
            $table->timestamps();
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('user_send')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organisation_send_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('user_received')->references('id')->on('users')->onDelete('set null');
            $table->foreign('organisation_received_id')->references('id')->on('organisations')->onDelete('set null');
            $table->foreign('mail_status_id')->references('id')->on('mail_status')->onDelete('cascade');
        });



        Schema::create('mail_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false)->unique();
            $table->timestamps();
        });


        Schema::create('mail_organisation', function (Blueprint $table) {
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('organisation_id')->nullable(false);
            $table->boolean('is_original')->nullable(false);
            $table->primary(['mail_id', 'organisation_id']);
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });


        Schema::create('container_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->primary('id');
        });


        Schema::create('mail_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->primary('id');
        });


        Schema::create('mail_containers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable(false);
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

        Schema::create('mail_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->primary('id');
            $table->string('duration');
        });


        Schema::create('mail_attachment', function (Blueprint $table) {
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('attachment_id')->nullable(false);
            $table->primary(['mail_id', 'attachment_id']);
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('mail_attachments')->onDelete('cascade');
        });


        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('path', 45)->nullable(false);
            $table->string('name', 45)->nullable(false);
            $table->string('crypt', 255)->nullable(false);
            $table->string('size', 10)->nullable(false);
            $table->primary('id');
        });


        Schema::create('mail_typologies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->primary('id');
            $table->unsignedBigInteger('class_id')->nullable(false);
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
        });


        Schema::create('mail_container', function (Blueprint $table) {
            $table->unsignedBigInteger('container_id')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->primary(['container_id', 'mail_id']);
            $table->foreign('container_id')->references('id')->on('mail_containers')->onDelete('cascade');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
        });


        Schema::create('mailbatches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->primary('id');
        });

        Schema::create('mailbatch_transaction', function (Blueprint $table) {
            $table->unsignedInteger('mailbatch_id')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('organisation_send_id')->nullable(false);
            $table->unsignedBigInteger('organisation_received_id')->nullable(false);
            $table->timestamps();
            $table->primary(['mailbatch_id', 'mail_id', 'organisation_send_id', 'organisation_received_id']);
            $table->foreign('mailbatch_id')->references('id')->on('mailbatchs')->onDelete('cascade');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('organisation_send_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('organisation_received_id')->references('id')->on('organisations')->onDelete('cascade');
        });




        /*


        Les paniers



        */



        Schema::create('dollies', function(Blueprint $table){
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('type_id')->nullable(false);
            $table->foreign('type_id')->references('id')->on('dolly_types')->onDelete('cascade');
        });

        Schema::create('dolly_types', function(Blueprint $table){
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->timestamps();
        });



         /*


        Thésaurus



        */




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

        Schema::create('relations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->primary('id');
        });

        Schema::create('term_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('child_id')->nullable(false);
            $table->unsignedInteger('category_id')->nullable(false);
            $table->unsignedInteger('relation_id')->nullable(false);
            $table->primary('id');
            $table->foreign('parent_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('term_categories')->onDelete('cascade');
            $table->foreign('relation_id')->references('id')->on('relations')->onDelete('cascade');
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
        Schema::dropIfExists('accessions');
        Schema::dropIfExists('accession_status');
        Schema::dropIfExists('containers');
        Schema::dropIfExists('files');
        Schema::dropIfExists('record_keyword');
        Schema::dropIfExists('record_links');
        Schema::dropIfExists('retention_activity');
        Schema::dropIfExists('organisation_activity');
        Schema::dropIfExists('retentions');
        Schema::dropIfExists('mail_typologies');
        Schema::dropIfExists('typology_categories');
        Schema::dropIfExists('mail_priorities');
        Schema::dropIfExists('communications');
        Schema::dropIfExists('mail_status');
        Schema::dropIfExists('mail_subjects');
        Schema::dropIfExists('mail_containers');
        Schema::dropIfExists('container_types');
        Schema::dropIfExists('mails');
        Schema::dropIfExists('mail_organisation');
        Schema::dropIfExists('mail_attachment');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('dollies');
        Schema::dropIfExists('mail_container');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('term_categories');
        Schema::dropIfExists('mailbatch_transaction');
        Schema::dropIfExists('mailbatchs');
        Schema::dropIfExists('user_office');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('sorts');
        Schema::dropIfExists('records');
        Schema::dropIfExists('user_types');
        Schema::dropIfExists('keywords');
        Schema::dropIfExists('dolly_types');
        Schema::dropIfExists('access_activities');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('communicabilities');
    }
};
