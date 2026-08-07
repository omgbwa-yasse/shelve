<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étape 8 — Alignement Constellio : collaboration sur les notices.
 *
 * - record_shares   : partage ad hoc d'une notice à un utilisateur (ou à un rôle
 *                     qui joue le rôle de groupe) avec permission et expiration.
 * - favorites       : favoris polymorphes (documents et dossiers), personnels ou
 *                     partagés.
 * - record_comments : commentaires génériques sur une notice (auteur seul
 *                     modifie/supprime).
 * - record_shortcuts: raccourci vers une notice existante sans dupliquer
 *                     `parent_id` (une notice peut avoir plusieurs raccourcis).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_shares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('permission', 20)->default('view'); // view | edit
            $table->dateTime('expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('record_id')->references('id')->on('records')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index('record_id');
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->morphs('favoriteable');
            $table->boolean('shared')->default(false);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'favoriteable_type', 'favoriteable_id']);
        });

        Schema::create('record_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id');
            $table->unsignedBigInteger('user_id');
            $table->text('content');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('record_id')->references('id')->on('records')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->index('record_id');
        });

        Schema::create('record_shortcuts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('label', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('record_id')->references('id')->on('records')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index('record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_shortcuts');
        Schema::dropIfExists('record_comments');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('record_shares');
    }
};
