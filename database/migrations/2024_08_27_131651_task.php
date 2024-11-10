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
         * Modules taches
         */

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 70)->unique()->nullable(false);
            $table->text('description')->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->unsignedBigInteger('task_type_id')->nullable(false);
            $table->unsignedBigInteger('task_status_id')->nullable(false);
            $table->date('start_date')->nullable();
            $table->unsignedBigInteger('parent_task_id')->nullable();
            $table->foreign('parent_task_id')->references('id')->on('tasks')->onDelete('set null');
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
            $table->id(); // Ajout d'une clé primaire
            $table->unsignedBigInteger('task_id')->nullable(false); // Ajout de la colonne task_id
            $table->date('date_fix')->nullable();
            $table->enum('periode', ['before', 'after'])->nullable();
            $table->enum('date_trigger', ['start', 'end'])->nullable();
            $table->integer('limit_number')->nullable();
            $table->date('limit_date')->nullable();
            $table->integer('frequence_value')->unsigned()->nullable(false);
            $table->enum('frequence_unit', ['year', 'month', 'day', 'hour'])->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Supervision des activités
        Schema::create('task_supervision', function (Blueprint $table) {
            $table->id(); // Ajout d'une clé primaire
            $table->unsignedBigInteger('task_id')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->boolean('task_assignation')->nullable();
            $table->boolean('task_update')->nullable();
            $table->boolean('task_parent_update')->nullable();
            $table->boolean('task_child_update')->nullable();
            $table->boolean('task_close')->nullable();
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_supervision');
        Schema::dropIfExists('task_remember');
        Schema::dropIfExists('task_attachment');
        Schema::dropIfExists('task_record');
        Schema::dropIfExists('task_container');
        Schema::dropIfExists('task_mail');
        Schema::dropIfExists('task_users');
        Schema::dropIfExists('task_organisations');
        Schema::dropIfExists('task_statues');
        Schema::dropIfExists('task_types');
        Schema::dropIfExists('tasks');
    }
};
