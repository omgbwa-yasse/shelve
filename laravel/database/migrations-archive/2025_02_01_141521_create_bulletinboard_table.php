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
                $table->string('name')->unique(true);
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('created_by')->references('id')->on('users');
            });


            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bulletin_board_id');
                $table->string('name')->unique(true);
                $table->text('description');
                $table->datetime('start_date');
                $table->datetime('end_date')->nullable();
                $table->string('location')->nullable();
                $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('bulletin_board_id')->references('id')->on('bulletin_boards')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users');
            });


            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(true);
                $table->text('description');
                $table->datetime('start_date');
                $table->datetime('end_date')->nullable();
                $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->softDeletes();
                $table->unsignedBigInteger('bulletin_board_id');
                $table->foreign('bulletin_board_id')->references('id')->on('bulletin_boards')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users');
            });


            Schema::create('post_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('post_id');
                $table->unsignedBigInteger('attachment_id');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
                $table->foreign('attachment_id')->references('id')->on('attachments');
                $table->foreign('created_by')->references('id')->on('users');
            });


            Schema::create('event_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('attachment_id');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->foreign('attachment_id')->references('id')->on('attachments');
                $table->foreign('created_by')->references('id')->on('users');
            });



            Schema::create('bulletin_board_organisation', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bulletin_board_id');
                $table->unsignedBigInteger('organisation_id');
                $table->unsignedBigInteger('assigned_by');
                $table->timestamps();
                $table->foreign('bulletin_board_id')->references('id')->on('bulletin_boards')->onDelete('cascade');
                $table->foreign('organisation_id')->references('id')->on('organisations');
                $table->foreign('assigned_by')->references('id')->on('users');
            });


            Schema::create('bulletin_board_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bulletin_board_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('role', ['super_admin', 'admin','moderator'])->default('admin');
                $table->enum('permissions', ['write', 'delete', 'edit'])->default('write');
                $table->unsignedBigInteger('assigned_by');
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('bulletin_board_id')->references('id')->on('bulletin_boards')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('assigned_by')->references('id')->on('users');
                $table->unique(['bulletin_board_id', 'user_id']);
            });

        }

        public function down()
        {
            Schema::dropIfExists('bulletin_board_user');
            Schema::dropIfExists('bulletin_board_organisation');
            Schema::dropIfExists('event_attachments');
            Schema::dropIfExists('post_attachments');
            Schema::dropIfExists('posts');
            Schema::dropIfExists('events');
            Schema::dropIfExists('bulletin_boards');
        }
};
