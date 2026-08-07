<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration de baseline — schéma complet de l'application.
 *
 * POURQUOI
 * --------
 * Les 128 migrations historiques ne rejouaient pas sur une base vierge :
 * `2023_10_21_000000_create_prompt_categories_table` déclare une clé étrangère vers
 * `organisations`, table créée par une migration postérieure (MySQL 1824). Aucun
 * environnement neuf ne pouvait donc être construit — ni CI, ni base de test, ni
 * Testcontainers pour la phase 3 de la migration (risque R24).
 *
 * Cette migration remplace l'historique par une photographie du schéma réel, prise
 * sur la base de développement. Les migrations d'origine sont conservées, non
 * chargées, dans `database/migrations-archive/`.
 *
 * COMPORTEMENT
 * ------------
 * - Base vierge   : charge `database/schema/baseline-schema.sql` (236 tables).
 *                   Le fichier est délibérément NOMMÉ autrement que `mysql-schema.sql` :
 *                   sous ce nom, Laravel le reconnaîtrait comme son squelette natif et
 *                   tenterait de le charger via le binaire `mysql`, absent du PATH sous
 *                   WAMP. Ici il est chargé par PDO, sans dépendance externe.
 * - Base existante: ne fait rien. Les environnements déjà en service ne sont pas
 *                   touchés ; la migration est simplement enregistrée comme jouée.
 *
 * REGÉNÉRATION
 * ------------
 *   scripts/setup-test-db.sh regenerate-schema
 *
 * À exécuter après toute nouvelle migration destinée à entrer dans la baseline.
 * Les migrations postérieures à celle-ci s'écrivent normalement, comme d'habitude.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Une base déjà en service possède son schéma : ne rien réappliquer.
        if (Schema::hasTable('users')) {
            return;
        }

        $path = database_path('schema/baseline-schema.sql');

        if (!is_file($path)) {
            throw new RuntimeException(
                "Squelette de schéma introuvable : $path\n" .
                'Le régénérer avec : scripts/setup-test-db.sh regenerate-schema'
            );
        }

        // Le squelette contient ses propres SET FOREIGN_KEY_CHECKS : les tables se
        // référencent mutuellement et l'ordre d'un dump ne suit pas les dépendances.
        DB::unprepared(file_get_contents($path));
    }

    public function down(): void
    {
        // Un `rollback` de la baseline reviendrait à supprimer la totalité du schéma.
        // `migrate:fresh` reste disponible pour repartir de zéro volontairement.
        throw new RuntimeException(
            "La migration de baseline ne peut pas être annulée : elle porte tout le schéma.\n" .
            'Utiliser `php artisan migrate:fresh` pour reconstruire une base.'
        );
    }
};
