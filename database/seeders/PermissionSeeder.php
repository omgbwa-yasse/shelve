<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Restaure le seeder des permissions — introuvable dans le dépôt (phase 1, D01).
 *
 * `SeedPermissions` (app/Console/Commands) et `GeneratePolicies` référencent tous deux
 * une classe `PermissionSeeder` qui n'existe nulle part dans le code source, alors que
 * `shelve_db` contient 345 permissions bien réelles. Le seeder d'origine a disparu du
 * dépôt sans que sa sortie ne soit perdue — exactement la même catégorie de problème
 * que R24 (schéma) : une source de vérité manquante alors que son résultat existe.
 *
 * Ce fichier restaure les 345 permissions telles qu'observées sur `shelve_db` en
 * 2026-08-04 (export : `database/seeders/permissions-export.json`, à titre d'archive),
 * et y ajoute les permissions D01 nécessaires aux Policies déjà présentes dans le code
 * mais jamais invoquées par les contrôleurs Blade (voir JOURNAL-PHASE-1.md).
 *
 * `updateOrInsert` : rejouable sans dupliquer ni écraser une permission déjà là.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $existing = json_decode(
            file_get_contents(__DIR__ . '/permissions-export.json'),
            true
        );

        $now = now();
        $count = 0;

        foreach ($existing as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [
                    'category' => $permission['category'],
                    'description' => $permission['description'],
                    'guard_name' => $permission['guard_name'] ?: 'web',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
            $count++;
        }

        $count += $this->seedD01ReferentialPermissions($now);

        $this->command?->info("$count permissions synchronisées.");
    }

    /**
     * Permissions D01 : les Policies (ActivityPolicy, LanguagePolicy, SortPolicy…)
     * attendent des permissions `{ressource}_{action}` qui n'ont jamais existé — ni
     * dans le code, ni en base — car aucun contrôleur Blade ne les invoquait. Ajoutées
     * ici pour que l'API v1 (qui, elle, applique les Policies) soit utilisable.
     *
     * Décision volontairement absente d'ici : QUELS rôles reçoivent ces permissions.
     * C'est une politique RBAC propre à l'organisation, pas un choix technique — elle
     * doit être prise par le propriétaire du domaine, pas décidée silencieusement par
     * un seeder de migration.
     */
    private function seedD01ReferentialPermissions($now): int
    {
        $resources = [
            'activity' => 'Activités',
            'language' => 'Langues',
            'sort' => 'Sorts finaux',
            'communicability' => 'Communicabilités',
            'keyword' => 'Mots-clés',
            'law' => 'Lois et articles de loi',
            'author' => 'Auteurs',
            'author_contact' => 'Contacts d\'auteurs',
            'external_contact' => 'Contacts externes',
            'external_organization' => 'Organisations externes',
            'setting' => 'Paramètres applicatifs',
            'setting_category' => 'Catégories de paramètres',
            'reference_list' => 'Listes de référence',
            'building' => 'Bâtiments',
            'floor' => 'Étages',
            'room' => 'Salles',
            'shelf' => 'Rayonnages',
            'container' => 'Conteneurs',
            'container_property' => 'Types de conteneurs',
            'container_status' => 'Statuts de conteneurs',
            // D02 — Records
            'record' => 'Notices',
            'record_attachment' => 'Pièces jointes de notices',
            'record_author' => 'Auteurs de notices',
            'record_child' => 'Notices enfants',
            'record_container' => 'Localisation de notices',
            'record_reactivation' => 'Réactivation de notices',
            'record_status' => 'Statuts de notices',
            'record_support' => 'Supports de notices',
            'record_digital_folder' => 'Dossiers numériques',
            'record_digital_document' => 'Documents numériques',
            'record_digital_transfer' => 'Transfert numérique→physique',
            'record_type' => 'Types de notices',
            'record_digital_folder_type' => 'Types de dossiers numériques',
            'record_digital_document_type' => 'Types de documents numériques',
            'metadata_definition' => 'Définitions de métadonnées',
            'folder_type_metadata_profile' => 'Profils de métadonnées (dossiers)',
            'document_type_metadata_profile' => 'Profils de métadonnées (documents)',
            // D04 — Versements
            'slip' => 'Bordereaux de versement',
            'slip_container' => 'Bordereaux - conteneurs',
            'slip_record' => 'Bordereaux - notices',
            'slip_record_attachment' => 'Bordereaux - pièces jointes',
            'slip_record_container' => 'Bordereaux - notices/conteneurs',
            'slip_status' => 'Statuts de bordereaux',
            'container_search' => 'Recherche de conteneurs',
            // D05 — Communications & réservations
            'communication' => 'Communications',
            'communication_record' => 'Communications - notices',
            'reservation' => 'Réservations',
            'reservation_record' => 'Réservations - notices',
            'activity_communicability' => 'Activité/communicabilité',
            // D06 — Courrier
            'mail' => 'Courriers',
            'mail_action' => 'Actions courrier',
            'mail_archive' => 'Archivage courrier',
            'mail_attachment' => 'Pièces jointes courrier',
            'mail_container' => 'Conteneurs courrier',
            'mail_container_transfer' => 'Transfert conteneurs courrier',
            'mail_priority' => 'Priorités courrier',
            'mail_received' => 'Courriers reçus',
            'mail_received_external' => 'Courriers reçus externes',
            'mail_send' => 'Courriers envoyés',
            'mail_send_external' => 'Courriers envoyés externes',
            'mail_transaction' => 'Transactions courrier',
            'mail_typology' => 'Typologies courrier',
            'batch' => 'Lots de courrier',
            'batch_handler' => 'Traitement de lots',
            'batch_received' => 'Lots reçus',
            'batch_send' => 'Lots envoyés',
            'batch_transfer' => 'Transfert de lots',
            // D07 — Cycle de vie
            'retention' => 'Durées de conservation',
            'retention_activity' => 'Conservation par activité',
            'retention_law_article' => 'Conservation par article de loi',
            'life_cycle' => 'Cycle de vie',
            'declassement_list' => 'Listes de déclassement',
            // D08 — Thésaurus
            'thesaurus' => 'Thésaurus',
            'thesaurus_scheme' => 'Schémas de thésaurus',
            'thesaurus_translation' => 'Traductions de thésaurus',
            'thesaurus_associative_relation' => 'Relations associatives',
            'thesaurus_search' => 'Recherche thésaurus',
            'thesaurus_import' => 'Import de thésaurus',
            // D10 — Recherche
            'search' => 'Recherche',
            'search_record' => 'Recherche notices',
            'search_mail' => 'Recherche courriers',
            'search_slip' => 'Recherche bordereaux',
            'search_communication' => 'Recherche communications',
            'search_reservation' => 'Recherche réservations',
            'search_dolly' => 'Recherche dolly',
            'search_mail_feedback' => 'Recherche retours courrier',
            // D11 — Dolly
            'dolly' => 'Dolly (paniers)',
            'dolly_action' => 'Actions dolly',
            'dolly_handler' => 'Traitement dolly',
            // D12 — Collaboration
            'workplace' => 'Espaces de travail',
            'workplace_activity' => 'Activité workplace',
            'workplace_bookmark' => 'Favoris workplace',
            'workplace_content' => 'Contenus workplace',
            'workplace_invitation' => 'Invitations workplace',
            'workplace_member' => 'Membres workplace',
            'workplace_message' => 'Messages workplace',
            'workplace_template' => 'Modèles workplace',
            'chat' => 'Chats',
            'task' => 'Tâches',
            // D13 — Workflow
            'workflow_definition' => 'Définitions de workflow',
            'workflow_instance' => 'Instances de workflow',
            // D14 — IA
            'ai_skill' => 'Compétences IA',
            'ai_template' => 'Modèles IA',
            'ai_resource' => 'Ressources IA',
            'ai_search' => 'Recherche IA',
            'prompt' => 'Prompts IA',
            'prompt_management' => 'Gestion des prompts',
            // D16 — Exploitation
            'backup' => 'Sauvegardes',
            'backup_file' => 'Fichiers de sauvegarde',
            'backup_planning' => 'Planification des sauvegardes',
            'log' => 'Journaux',
            'new_feature' => 'Nouvelles fonctionnalités',
            'rate_limit' => 'Limites de débit',
            'system_update' => 'Mises à jour système',
            'report' => 'Rapports',
            'barcode' => 'Codes-barres',
            'pdf' => 'PDF',
        ];

        $abilities = [
            'viewAny' => 'Consulter la liste',
            'view' => 'Consulter le détail',
            'create' => 'Créer',
            'update' => 'Modifier',
            'delete' => 'Supprimer',
            'force_delete' => 'Supprimer définitivement',
        ];

        $count = 0;

        foreach ($resources as $resource => $label) {
            foreach ($abilities as $ability => $actionLabel) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => "{$resource}_{$ability}"],
                    [
                        'category' => 'settings',
                        'description' => "$actionLabel — $label (D01)",
                        'guard_name' => 'web',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
                $count++;
            }
        }

        return $count;
    }
}
