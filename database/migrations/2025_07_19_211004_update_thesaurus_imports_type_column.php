<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Modifie la colonne type pour accepter la nouvelle valeur 'skos-rdf' suite à
     * la correction de compréhension que SKOS est exprimé en RDF.
     */
    public function up(): void
    {
        // Approche en plusieurs étapes pour éviter les problèmes de données

        // 1. Ajouter une nouvelle colonne temporaire
        Schema::table('thesaurus_imports', function (Blueprint $table) {
            $table->string('new_type', 20)->nullable()->after('type');
        });

        // 2. Convertir les données
        DB::statement("UPDATE thesaurus_imports SET new_type = CASE
                WHEN type = 'skos' OR type = 'rdf' THEN 'skos-rdf'
                ELSE type
            END");

        // 3. Supprimer l'ancienne colonne
        Schema::table('thesaurus_imports', function (Blueprint $table) {
            // Supprimer l'index s'il existe
            if (Schema::hasIndex('thesaurus_imports', 'thesaurus_imports_type_index')) {
                $table->dropIndex('thesaurus_imports_type_index');
            }
            $table->dropColumn('type');
        });

        // 4. Renommer la nouvelle colonne
        Schema::table('thesaurus_imports', function (Blueprint $table) {
            $table->renameColumn('new_type', 'type');
        });

        // 5. Modifier le type de la colonne pour qu'elle soit un ENUM et ajouter l'index
        DB::statement("ALTER TABLE thesaurus_imports MODIFY COLUMN type ENUM('skos-rdf', 'csv', 'json') NOT NULL COMMENT 'Type d\\'import'");

        Schema::table('thesaurus_imports', function (Blueprint $table) {
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Ajouter une colonne temporaire
        Schema::table('thesaurus_imports', function (Blueprint $table) {
            $table->string('old_type', 20)->nullable()->after('type');
        });

        // 2. Convertir les données (avec perte d'information)
        DB::statement("UPDATE thesaurus_imports SET old_type = CASE
                WHEN type = 'skos-rdf' THEN 'skos'
                ELSE type
            END");

        // 3. Supprimer l'ancienne colonne
        Schema::table('thesaurus_imports', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });

        // 4. Renommer la nouvelle colonne
        Schema::table('thesaurus_imports', function (Blueprint $table) {
            $table->renameColumn('old_type', 'type');
        });

        // 5. Modifier le type de la colonne pour qu'elle soit un ENUM et ajouter l'index
        DB::statement("ALTER TABLE thesaurus_imports MODIFY COLUMN type ENUM('skos', 'rdf', 'csv', 'json') NOT NULL COMMENT 'Type d\\'import'");

        Schema::table('thesaurus_imports', function (Blueprint $table) {
            $table->index('type');
        });
    }
};
