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
use Database\Seeders\Settings\SuperAdminSeeder;
use Database\Seeders\Tools\ToolActivitySeeder;
use Database\Seeders\Tools\ToolCommunicabilitySeeder;
use Database\Seeders\Tools\ToolOrganisationSeeder;
use Database\Seeders\Settings\SettingSeeder;
use Database\Seeders\AI\AiProvidersSeeder;
use Database\Seeders\AI\PromptSeeder;
use Database\Seeders\Public\OpacConfigurationSeeder;
use Database\Seeders\Public\OpacTemplateSeeder;
use Database\Seeders\Tools\KeywordSeeder;
use Database\Seeders\Records\ExampleData\RecordDigitalFolderSeeder;
use Database\Seeders\Records\ExampleData\RecordDigitalDocumentSeederSimple;

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

            // 6. UTILISATEURS (Dépendent des organisations et permissions)
            SuperAdminSeeder::class, // Seeder pour créer le superadmin avec toutes les permissions

            // 7. ACTIVITÉS ET SERVICES (Dépendent des organisations)
            ToolActivitySeeder::class, // Seeder pour les activités
            ToolCommunicabilitySeeder::class, // Seeder pour les règles de communicabilité
            ToolOrganisationSeeder::class, // Seeder pour les organisations et services

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

            // 11. SYSTÈME DE MÉTADONNÉES (Définitions et profils)
            // MetadataSystemSeeder::class, // Définitions de métadonnées et listes de référence - Déjà exécuté
            // DocumentFolderTypesWithMetadataSeeder::class, // Types de documents/dossiers avec profils de métadonnées - À corriger

            // 12. DONNÉES D'EXEMPLE (Optionnel - après toute la structure)
            // RecordSeederSimple::class, // Exemples de documents d'archives - Nécessite la table 'records'
            // BooksSeeder::class, // Exemples de livres - Supprimé
            KeywordSeeder::class, // Mots-clés pour les dossiers/documents numériques
            RecordDigitalFolderSeeder::class, // Exemples de dossiers numériques (Phase 3)
            RecordDigitalDocumentSeederSimple::class, // Exemples de documents numériques (Phase 3)

            // 13. MODULE OPAC COMPLET (Pages, événements, utilisateurs publics)
            // OpacSeeder::class, // Seeder complet pour l'OPAC - Nécessite les tables OPAC
        ]);
    }
}
