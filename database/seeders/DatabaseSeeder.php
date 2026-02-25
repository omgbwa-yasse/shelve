<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Settings\PermissionCategorySeeder;
use Database\Seeders\Contacts\ExternalContactsSeeder;
use Database\Seeders\Contacts\AuthorTypeSeeder;
use Database\Seeders\Contacts\AddressTypeSeeder;
use Database\Seeders\Mails\MailSystemSeeder;
use Database\Seeders\Tools\ThesaurusTypologieSeeder;
use Database\Seeders\Tools\ThesaurusMatiereSeeder;
use Database\Seeders\Tools\ThesaurusGeographiqueSeeder;
use Database\Seeders\Tools\ThesaurusActionsAdministrativesSeeder;
use Database\Seeders\Records\Configuration\RecordStatusSeeder;
use Database\Seeders\Records\Configuration\RecordLevelSeeder;
use Database\Seeders\Records\Configuration\RecordSupportSeeder;
use Database\Seeders\Deposits\ContainerStatusSeeder;
use Database\Seeders\Deposits\SortSeeder;
use Database\Seeders\Transfers\SlipStatusSeeder;
use Database\Seeders\Workplaces\OrganisationSeeder;
use Database\Seeders\Workplaces\OrganisationServicesSeeder;
use Database\Seeders\Workplaces\OrganisationRoomSeeder;
use Database\Seeders\Workplaces\WorkplaceCategorySeeder;
use Database\Seeders\Settings\SuperAdminSeeder;
use Database\Seeders\Tools\ToolActivitySeeder;
use Database\Seeders\Tools\ToolCommunicabilitySeeder;
use Database\Seeders\Settings\SettingSeeder;
use Database\Seeders\AI\AiProvidersSeeder;
use Database\Seeders\AI\PromptSeeder;
use Database\Seeders\Public\OpacConfigurationSeeder;
use Database\Seeders\Public\OpacTemplateSeeder;
use Database\Seeders\Tools\KeywordSeeder;
use Database\Seeders\Records\ExampleData\RecordDigitalFolderSeeder;
use Database\Seeders\Records\ExampleData\RecordDigitalDocumentSeederSimple;

// Module Data Seeders (test data for all 11 modules)
use Database\Seeders\Contacts\ContactDataSeeder;
use Database\Seeders\Tools\RetentionLawSeeder;
use Database\Seeders\Records\RecordDataSeeder;
use Database\Seeders\Deposits\DepositDataSeeder;
use Database\Seeders\Mails\MailDataSeeder;
use Database\Seeders\Workflow\WorkflowDataSeeder;
use Database\Seeders\Workplaces\WorkplaceDataSeeder;
use Database\Seeders\Transfers\TransferDataSeeder;
use Database\Seeders\Communications\CommunicationSeeder;
use Database\Seeders\Dollies\DollySeeder;
use Database\Seeders\PublicPortal\PublicDataSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. PERMISSIONS (Base du système d'authentification)
            PermissionCategorySeeder::class, // Seeder avec catégories et nomenclature unifiée

            // 2. DONNÉES DE BASE (Indépendantes)
            ExternalContactsSeeder::class, // Seeder pour les contacts externes
            MailSystemSeeder::class, // Seeder pour le système de courriers
            AuthorTypeSeeder::class, // Seeder pour les types d'auteurs
            AddressTypeSeeder::class, // Seeder pour les types d'adresses

            // 3. THÉSAURUS (Indépendants)
            ThesaurusTypologieSeeder::class, // Seeder pour le thésaurus des typologies documentaires
            ThesaurusMatiereSeeder::class,   // Seeder pour le thésaurus des matières
            ThesaurusGeographiqueSeeder::class, // Seeder pour le thésaurus géographique
            ThesaurusActionsAdministrativesSeeder::class, // Seeder pour le thésaurus des actions administratives

            // 4. DONNÉES RECORDS (Indépendantes)
            RecordStatusSeeder::class, // Seeder pour les statuts des dossiers
            RecordLevelSeeder::class,  // Seeder pour les niveaux hiérarchiques
            RecordSupportSeeder::class, // Seeder pour les supports physiques
            ContainerStatusSeeder::class, // Seeder pour les statuts des contenants
            SortSeeder::class, // Seeder pour les sorts finaux (E, T, C)
            SlipStatusSeeder::class, // Seeder pour les statuts de bordereaux

            // 5. ORGANISATIONS (Structure organisationnelle + Infrastructure physique)
            OrganisationSeeder::class, // Seeder pour les organisations et infrastructure

            // 5bis. SERVICES ET BUREAUX (Dépendent des organisations principales)
            OrganisationServicesSeeder::class, // Seeder pour créer les services et bureaux

            // 5ter. CATÉGORIES WORKPLACE
            WorkplaceCategorySeeder::class, // Catégories pour les espaces de travail

            // 6. UTILISATEURS (Dépendent des organisations et permissions)
            SuperAdminSeeder::class, // Seeder pour créer le superadmin avec toutes les permissions

            // 7. ACTIVITÉS ET SERVICES (Dépendent des organisations)
            ToolActivitySeeder::class, // Seeder pour les activités
            ToolCommunicabilitySeeder::class, // Seeder pour les règles de communicabilité

            // 7bis. AFFECTATION SALLES AUX ORGANISATIONS
            OrganisationRoomSeeder::class, // Seeder pour affecter les salles aux organisations

            // 8. PARAMÈTRES APPLICATIFS (Indépendants)
            SettingSeeder::class, // Paramètres et catégories de paramètres (idempotent)

            // 9. IA ET PROMPTS (Indépendants)
            AiProvidersSeeder::class, // Paramètres des providers IA (settings)
            PromptSeeder::class, // Prompts système pour l'IA

            // 10. CONFIGURATION OPAC (Dépend des organisations)
            OpacConfigurationSeeder::class, // Configuration OPAC par organisation
            OpacTemplateSeeder::class, // Templates OPAC disponibles

            // 11. DONNÉES D'EXEMPLE NUMÉRIQUES
            KeywordSeeder::class, // Mots-clés pour les dossiers/documents numériques
            RecordDigitalFolderSeeder::class, // Exemples de dossiers numériques (Phase 3)
            RecordDigitalDocumentSeederSimple::class, // Exemples de documents numériques (Phase 3)

            // ============================================================
            // 12. DONNÉES DE TEST PAR MODULE (idempotentes — firstOrCreate)
            //     Ordre : Contacts → Outils → Dépôts → Répertoire → Courriers
            //     → Workflow → Espaces de travail → Transferts → Communications
            //     → Chariots → Portail public
            // ============================================================
            ContactDataSeeder::class,       // Auteurs, contacts, organisations externes
            RetentionLawSeeder::class,      // Sorts, lois, articles, règles de conservation
            DepositDataSeeder::class,       // Propriétés, statuts et conteneurs
            RecordDataSeeder::class,        // Hiérarchie ISAD(G) + versements
            MailDataSeeder::class,          // Courriers (internes/entrants/sortants), lots, métriques
            WorkflowDataSeeder::class,      // Définitions, instances, tâches de workflow
            WorkplaceDataSeeder::class,     // Espaces de travail, membres, dossiers
            TransferDataSeeder::class,      // Bordereaux de versement + articles
            CommunicationSeeder::class,     // Communications + réservations
            DollySeeder::class,             // Types et chariots multi-catégories
            PublicDataSeeder::class,        // Utilisateurs publics, actualités, pages, événements, chat, feedback
        ]);
    }
}
