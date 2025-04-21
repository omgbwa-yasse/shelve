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
            Les chariots
        */

        Schema::create('dolly_mails', function(Blueprint $table){
            $table->unsignedBigInteger('mail_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
            $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create('dolly_mail_transactions', function(Blueprint $table){
            $table->unsignedBigInteger('mail_transaction_id')->nullable(false);
            $table->unsignedBigInteger('dolly_id')->nullable(false);
            $table->foreign('mail_transaction_id')->references('id')->on('mail_transactions')->onDelete('cascade');
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
            $table->enum('category', ['mail', 'transaction', 'record', 'slip', 'building', 'shelf', 'container', 'communication', 'room']);
            $table->boolean('is_public')->default(false);
            $table->unsignedBigInteger('created_by')->nullable(false);
            $table->unsignedBigInteger('owner_organisation_id')->nullable(false);
            $table->timestamps();
            $table->foreign('owner_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dolly_mails');
        Schema::dropIfExists('dolly_mail_transactions');
        Schema::dropIfExists('dolly_records');
        Schema::dropIfExists('dolly_slips');
        Schema::dropIfExists('dolly_slip_records');
        Schema::dropIfExists('dolly_buildings');
        Schema::dropIfExists('dolly_shelves');
        Schema::dropIfExists('dolly_containers');
        Schema::dropIfExists('dolly_communications');
        Schema::dropIfExists('dolly_rooms');
        Schema::dropIfExists('dollies');
        Schema::dropIfExists('dolly_types');
    }
};

