<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
            SortSeeder::class, // Seeder pour les sorts finaux (E, T, C)

            // 5. ORGANISATIONS (Structure organisationnelle + Infrastructure physique)
            OrganisationSeeder::class, // Seeder pour les organisations et infrastructure

            // 5bis. SERVICES ET BUREAUX (Dépendent des organisations principales)
            OrganisationServicesSeeder::class, // Seeder pour créer les services et bureaux

            // 6. UTILISATEURS (Dépendent des organisations et permissions)
            SuperAdminSeeder::class, // Seeder pour créer le superadmin avec toutes les permissions

            // 7. ACTIVITÉS ET SERVICES (Dépendent des organisations)
            ToolActivitySeeder::class, // Seeder pour les activités
            OrganisationServicesSeeder::class, // Seeder pour créer services et bureaux
            ToolCommunicabilitySeeder::class, // Seeder pour les règles de communicabilité
            ToolOrganisationSeeder::class, // Seeder pour les organisations et services

            // 7bis. AFFECTATION SALLES AUX ORGANISATIONS
            OrganisationRoomSeeder::class, // Seeder pour affecter les salles aux organisations

            // 8. PARAMÈTRES APPLICATIFS (Indépendants)
            SettingSeeder::class, // Paramètres et catégories de paramètres (idempotent)

            // 9. IA ET PROMPTS (Indépendants)
            AiSeeder::class,
            AiProvidersSeeder::class, // Paramètres des providers IA (settings)
        ]);
    }
}
