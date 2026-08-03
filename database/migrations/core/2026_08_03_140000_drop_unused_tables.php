<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nettoyage : suppression de tables créées par d'anciennes migrations mais
     * jamais utilisées — aucune ligne en base, aucun modèle Eloquent, aucune
     * référence dans app/, routes/, resources/ ni database/seeders.
     *
     * Le détail de leur schéma reste disponible dans les migrations d'origine
     * (conservées) et dans l'historique git.
     *
     * Volontairement CONSERVÉES bien que vides :
     *  - cache_locks, personal_access_tokens  → utilisées par le framework ;
     *  - communication_statuses, container_types → encore visées par une contrainte
     *    FK résiduelle sous SQLite ;
     *  - bulletin_boards → référencée par events (portail public) ;
     *  - prompt_categories → référencée par prompts ;
     *  - hierarchical_relations → utilisée par l'import RDF du thésaurus ;
     *  - llm_daily_stats → alimentée par la commande LlmAggregateDaily ;
     *  - record_periodic* → module périodiques (service + API existants).
     */
    private const TABLES = [
        // Module LDAP jamais développé (nom mal orthographié à l'origine)
        'ladp_distribution',
        'ladp_clients',
        'ladp_contents',
        'ladp_servers',

        // Reliquat du module workflow retiré en 2025
        'mail_workflows',

        // Module « ouvrages » jamais développé
        'dolly_books',
        'record_book_classification',
        'record_book_subject',

        // Tableau d'affichage : pivots et pièces jointes jamais branchés
        'post_attachments',
        'posts',
        'bulletin_board_organisation',
        'bulletin_board_user',

        // Satellites orphelins
        'author_addresses',
        'event_attachments',
        'record_physical_links',

        // Tables du thésaurus prévues puis abandonnées (l'import RDF ne les alimente pas)
        'collection_members',
        'concept_history',
        'external_alignments',
        'mapping_relations',
        'scheme_properties',
        'non_descriptors',
    ];

    public function up(): void
    {
        // Les tables sont listées enfants d'abord ; on neutralise tout de même les
        // contraintes le temps de la suppression (bases anciennes, ordres exotiques).
        Schema::disableForeignKeyConstraints();

        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Pas de restauration : ces tables étaient vides et inutilisées. Leur schéma
     * figure dans les migrations d'origine si le besoin réapparaissait.
     */
    public function down(): void
    {
        // no-op volontaire
    }
};
