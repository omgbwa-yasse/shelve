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
            Les localisations des archives
        */


        Schema::create('organisation_room', function (Blueprint $table) {
            $table->bigInteger('room_id')->unsigned()->notNull();
            $table->bigInteger('organisation_id')->unsigned()->notNull();
            $table->primary(['room_id', 'organisation_id']);
            $table->timestamps();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });



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
            $table->string('code', 10);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('floor_id');
            $table->unsignedBigInteger('creator_id');
            $table->unsignedBigInteger('type_id');
            $table->timestamps();
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['archives', 'producer']);
            $table->text('description')->nullable();
            $table->timestamps();
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
            $table->string('code', 20)->nullable(false)->unique();
            $table->unsignedBigInteger('shelve_id')->nullable(false);
            $table->unsignedBigInteger('status_id')->nullable(false);
            $table->unsignedBigInteger('property_id')->nullable(false);
            $table->unsignedBigInteger('creator_id')->nullable(false);
            $table->unsignedBigInteger('creator_organisation_id')->nullable(false);
            $table->boolean('is_archived')->nullable(false)->default(false);
            $table->timestamps();
            $table->foreign('creator_organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shelve_id')->references('id')->on('shelves')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('container_status')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('container_properties')->onDelete('cascade');
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


        Schema::create('container_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false);
            $table->string('description', 100)->nullable();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_');
    }
};
