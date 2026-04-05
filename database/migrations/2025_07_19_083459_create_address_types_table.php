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
        Schema::create('address_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable(false)->unique();
            $table->longText('description')->nullable(false);
            $table->timestamps();
        });

        // Une fois la table address_types créée, nous pouvons mettre à jour
        // la contrainte de clé étrangère de la table author_addresses
        if (collect(Schema::getForeignKeys('author_addresses'))->contains('name', 'author_addresses_type_id_foreign')) {
            Schema::table('author_addresses', function (Blueprint $table) {
                $table->dropForeign(['type_id']);
            });
        }

        Schema::table('author_addresses', function (Blueprint $table) {
            // Ensuite, ajoutez la nouvelle contrainte vers address_types
            $table->foreign('type_id')->references('id')->on('address_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurez la clé étrangère originale avant de supprimer la table address_types
        Schema::table('author_addresses', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->foreign('type_id')->references('id')->on('author_types')->onDelete('cascade');
        });

        Schema::dropIfExists('address_types');
    }
};
