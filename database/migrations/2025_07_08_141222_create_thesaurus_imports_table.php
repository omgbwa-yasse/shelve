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
        Schema::create('thesaurus_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['skos', 'rdf', 'csv', 'json'])->comment('Type d\'import');
            $table->string('filename', 255)->comment('Nom du fichier importé');
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->integer('total_items')->default(0)->comment('Nombre total d\'éléments à traiter');
            $table->integer('processed_items')->default(0)->comment('Nombre d\'éléments traités');
            $table->integer('created_items')->default(0)->comment('Nombre d\'éléments créés');
            $table->integer('updated_items')->default(0)->comment('Nombre d\'éléments mis à jour');
            $table->integer('error_items')->default(0)->comment('Nombre d\'éléments en erreur');
            $table->integer('relationships_created')->default(0)->comment('Nombre de relations créées');
            $table->text('message')->nullable()->comment('Message de statut ou d\'erreur');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thesaurus_imports');
    }
};
