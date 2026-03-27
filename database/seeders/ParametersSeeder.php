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

/**
 * ParametersSeeder
 *
 * Seeds only base configuration data (permissions, reference tables,
 * organisations, superadmin, application settings, AI providers, OPAC config).
 *
 * Does NOT seed test/demo module data (contacts, records, mails, etc.).
 * Safe to run multiple times — all underlying seeders use firstOrCreate/updateOrInsert.
 */
class ParametersSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. PERMISSIONS (Base du système d'authentification)
            PermissionCategorySeeder::class,

            // 2. DONNÉES DE BASE (Indépendantes)
            ExternalContactsSeeder::class,
            MailSystemSeeder::class,
            AuthorTypeSeeder::class,
            AddressTypeSeeder::class,

            // 3. THÉSAURUS (Indépendants)
            ThesaurusTypologieSeeder::class,
            ThesaurusMatiereSeeder::class,
            ThesaurusGeographiqueSeeder::class,
            ThesaurusActionsAdministrativesSeeder::class,

            // 4. DONNÉES RECORDS (Indépendantes)
            RecordStatusSeeder::class,
            RecordLevelSeeder::class,
            RecordSupportSeeder::class,
            SortSeeder::class,
            SlipStatusSeeder::class,

            // 5. ORGANISATIONS (Structure organisationnelle + Infrastructure physique)
            OrganisationSeeder::class,

            // 5bis. SERVICES ET BUREAUX
            OrganisationServicesSeeder::class,

            // 5ter. CATÉGORIES WORKPLACE
            WorkplaceCategorySeeder::class,

            // 6. UTILISATEURS (Besoin des organisations)
            SuperAdminSeeder::class,

            // 6bis. DONNÉES DÉPENDANTES DES UTILISATEURS
            ContainerStatusSeeder::class,

            // 7. ACTIVITÉS ET SERVICES
            ToolActivitySeeder::class,
            ToolCommunicabilitySeeder::class,

            // 7bis. AFFECTATION SALLES AUX ORGANISATIONS
            OrganisationRoomSeeder::class,

            // 8. PARAMÈTRES APPLICATIFS
            SettingSeeder::class,

            // 9. IA ET PROMPTS
            AiProvidersSeeder::class,
            PromptSeeder::class,

            // 10. CONFIGURATION OPAC
            OpacConfigurationSeeder::class,
            OpacTemplateSeeder::class,

            // 11. DONNÉES D'EXEMPLE NUMÉRIQUES
            KeywordSeeder::class,
            RecordDigitalFolderSeeder::class,
            RecordDigitalDocumentSeederSimple::class,
        ]);
    }
}
