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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Nom du template');
            $table->string('slug', 100)->unique()->comment('Identifiant unique du template');
            $table->text('description')->nullable()->comment('Description du template');
            $table->enum('type', ['opac', 'public', 'admin', 'email'])->default('opac')->comment('Type de template');
            $table->enum('status', ['draft', 'active', 'inactive', 'archived'])->default('draft')->comment('Statut du template');
            $table->longText('content')->nullable()->comment('Contenu HTML du template');
            $table->json('settings')->nullable()->comment('Paramètres de configuration');
            $table->string('theme', 50)->default('default')->comment('Thème associé');
            $table->boolean('is_default')->default(false)->comment('Template par défaut');
            $table->string('created_by')->nullable()->comment('Créateur du template');
            $table->string('updated_by')->nullable()->comment('Dernière personne à l\'avoir modifié');

            // Champs avancés pour l'éditeur
            $table->longText('layout')->nullable()->comment('Structure HTML du template');
            $table->longText('custom_css')->nullable()->comment('CSS personnalisé du template');
            $table->longText('custom_js')->nullable()->comment('JavaScript personnalisé du template');
            $table->json('variables')->nullable()->comment('Variables de configuration du template');
            $table->json('components')->nullable()->comment('Composants actifs dans le template');
            $table->string('version', 10)->default('1.0.0')->comment('Version du template');
            $table->json('meta')->nullable()->comment('Métadonnées additionnelles du template');
            $table->timestamp('last_modified')->nullable()->comment('Dernière modification du contenu');
            $table->string('modified_by')->nullable()->comment('Utilisateur ayant modifié en dernier');

            $table->timestamps();

            $table->index(['type', 'status'], 'template_type_status');
            $table->index('is_default', 'template_default');
            $table->index('slug', 'template_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
