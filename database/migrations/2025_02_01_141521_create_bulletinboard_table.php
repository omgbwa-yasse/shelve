<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
        {
            Schema::create('bulletin_boards', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('user_id')->references('id')->on('users');
            });


            Schema::create('events', function (Blueprint $table) {  // Pas besoin de préfixe car lié via relation
                $table->id();
                $table->unsignedBigInteger('bulletin_board_id');
                $table->string('name');
                $table->text('description');
                $table->datetime('start_date');
                $table->datetime('end_date')->nullable();
                $table->string('location')->nullable();
                $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('bulletin_board_id')->references('id')->on('bulletin_boards')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users');
            });


            Schema::create('posts', function (Blueprint $table) {  // Pas besoin de préfixe car lié via relation
                $table->id();
                $table->string('name');
                $table->text('description');
                $table->datetime('start_date');
                $table->datetime('end_date')->nullable();
                $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->unsignedBigInteger('bulletin_board_id');
                $table->foreign('bulletin_board_id')->references('id')->on('bulletin_boards')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users');
            });



            Schema::create('bulletin_board_attachment', function (Blueprint $table) {  // Pas besoin de préfixe
                $table->id();
                $table->unsignedBigInteger('bulletin_board_id');
                $table->unsignedBigInteger('attachment_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->foreign('bulletin_board_id')->references('id')->on('bulletin_boards')->onDelete('cascade');
                $table->foreign('attachment_id')->references('id')->on('attachments');
                $table->foreign('user_id')->references('id')->on('users');
            });


            Schema::create('bulletin_board_organisation', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bulletin_board_id');
                $table->unsignedBigInteger('organisation_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->foreign('bulletin_board_id')->references('id')->on('bulletin_boards')->onDelete('cascade');
                $table->foreign('organisation_id')->references('id')->on('organisations');
            });


            Schema::create('bulletin_board_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bulletin_board_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('role', ['super_admin', 'admin','moderator'])->default('admin');
                $table->enum('permissions', ['write', 'delete', 'edit'])->default('write');
                $table->unsignedBigInteger('assigned_by_id');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('bulletin_board_id')->references('id')->on('bulletin_boards')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('assigned_by_id')->references('id')->on('users');

                $table->unique(['bulletin_board_id', 'user_id']);
            });

        }

        public function down()
        {

            Schema::dropIfExists('poster_organisations');
            Schema::dropIfExists('poster_attachments');
            Schema::dropIfExists('poster_events');
            Schema::dropIfExists('posters');
        }
};
