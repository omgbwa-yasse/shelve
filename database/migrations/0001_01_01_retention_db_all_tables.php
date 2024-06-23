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






        /*


            Les localisations des archives



        */




        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->primary('id');
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
            $table->primary('id');
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
            $table->primary('id');
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
            $table->primary('id');
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
            $table->string('name', 40)->nullable(false);
            $table->text('description')->nullable();
            $table->primary('id');
            $table->unique('name');
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->timestamps();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
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


        Schema::create('record_author', function (Blueprint $table) {
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('record_id');
            $table->primary(['author_id', 'record_id']);
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
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


        Schema::create('user_organisation', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organisation_id');
            $table->boolean('active')->default(false);
            $table->primary(['user_id', 'organisation_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
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
            $table->string('code')->nullable(false)->unique();
            $table->string('name', 255)->nullable(false);
            $table->text('author')->nullable(false);
            $table->text('contacts')->nullable(false);
            $table->text('description')->nullable(true);
            $table->date('date')->nullable(false);
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


        Schema::create('mail_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->nullable(false);
            $table->datetime('date_creation')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('user_send_id')->nullable(false);
            $table->unsignedBigInteger('organisation_send_id')->nullable(false);
            $table->unsignedBigInteger('user_received_id')->nullable();
            $table->unsignedBigInteger('organisation_received_id')->nullable();
            $table->unsignedBigInteger('mail_type_id')->nullable(false);
            $table->unsignedBigInteger('document_type_id')->nullable(false);
            $table->timestamps();
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('user_send')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organisation_send_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('user_received')->references('id')->on('users')->onDelete('set null');
            $table->foreign('organisation_received_id')->references('id')->on('organisations')->onDelete('set null');
            $table->foreign('mail_type_id')->references('id')->on('mail_types')->onDelete('cascade');
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
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
            $table->string('name', 50)->nullable(false);
            $table->primary('id');
        });


        Schema::create('mail_archiving', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('container_id')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('document_type_id')->nullable(false);
            $table->primary('id');
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
            $table->primary('id');
            $table->foreign('type_id')->references('id')->on('container_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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


        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('path', 100)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->string('crypt', 255)->nullable(false);
            $table->integer('size', 50)->nullable(false);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->timestamps();
            $table->primary('id');
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
            $table->primary('id');
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
            $table->longText('description', 50)->nullable(false);
            $table->timestamps();
            $table->primary('id');
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
            $table->primary('id');
            $table->foreign('parent_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('type_id')->references('id')->on('author_types')->onDelete('cascade');
        });

        Schema::create('author_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false)->unique();
            $table->longText('description')->nullable(false);
            $table->timestamps();
            $table->primary('id');
        });

        Schema::create('container_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
            $table->primary('id');
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
            $table->primary('id');
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
            $table->primary('id');
            $table->timestamps();
        });


        Schema::create('batch_mail', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('batch_id')->nullable(false);
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->dateTime('insertion_date')->nullable(false);
            $table->dateTime('exit_date')->nullable(true);
            $table->timestamps();
            $table->primary('id');
            $table->foreign('batch_id')->references('id')->on('mailbatchs')->onDelete('cascade');
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
        });


        Schema::create('batch_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('batch_id')->nullable(false);
            $table->unsignedBigInteger('organisation_send_id')->nullable(false);
            $table->unsignedBigInteger('organisation_received_id')->nullable(false);
            $table->timestamps();
            $table->primary('id');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
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
        Schema::dropIfExists('access_activities');
        Schema::dropIfExists('accessions');
        Schema::dropIfExists('accession_status');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('container_status');
        Schema::dropIfExists('container_types');
        Schema::dropIfExists('containers');
        Schema::dropIfExists('communicabilities');
        Schema::dropIfExists('dolly_types');
        Schema::dropIfExists('dollies');
        Schema::dropIfExists('files');
        Schema::dropIfExists('floors');
        Schema::dropIfExists('keywords');
        Schema::dropIfExists('mail_attachment');
        Schema::dropIfExists('mail_container');
        Schema::dropIfExists('mail_containers');
        Schema::dropIfExists('mail_organisation');
        Schema::dropIfExists('mail_priorities');
        Schema::dropIfExists('mail_status');
        Schema::dropIfExists('mail_typologies');
        Schema::dropIfExists('mail_author');
        Schema::dropIfExists('mails');
        Schema::dropIfExists('organisation_activity');
        Schema::dropIfExists('organisations');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('record_keyword');
        Schema::dropIfExists('record_links');
        Schema::dropIfExists('record_levels');
        Schema::dropIfExists('record_status');
        Schema::dropIfExists('records');
        Schema::dropIfExists('record_author');
        Schema::dropIfExists('relations');
        Schema::dropIfExists('relation_types');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('sorts');
        Schema::dropIfExists('term_categories');
        Schema::dropIfExists('term_record');
        Schema::dropIfExists('term_relations');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('typology_categories');
        Schema::dropIfExists('user_office');
        Schema::dropIfExists('user_types');
        Schema::dropIfExists('user_organisation');
        Schema::dropIfExists('document_types');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('authors_types');
    }
};
