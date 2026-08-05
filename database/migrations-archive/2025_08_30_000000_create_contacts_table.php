<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Table principale des contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // Type de contact (enum requis)
            $table->enum('type', ['email', 'telephone', 'gps', 'fax', 'code_postal', 'adresse']);

            // Valeur du contact (ex: adresse e-mail, numéro, coordonnées...)
            $table->text('value');

            // Optionnel: notes libres / label
            $table->string('label', 190)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Index utiles
            $table->index('type');
        });

        // Table pivot entre organisations et contacts
        Schema::create('organisation_contact', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('contact_id');

            // Empêcher les doublons de liaison
            $table->unique(['organisation_id', 'contact_id'], 'org_contact_unique');

            // Contraintes référentielles
            $table->foreign('organisation_id')
                ->references('id')->on('organisations')
                ->cascadeOnDelete();

            $table->foreign('contact_id')
                ->references('id')->on('contacts')
                ->cascadeOnDelete();

            // Index pour filtrage
            $table->index('organisation_id');
            $table->index('contact_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_contact');
        Schema::dropIfExists('contacts');
    }
};
