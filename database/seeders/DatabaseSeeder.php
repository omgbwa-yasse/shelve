<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Seeders pour les permissions et utilisateurs
            PermissionCategorySeeder::class, // Seeder avec catégories et nomenclature unifiée
            SuperAdminSeeder::class, // Seeder pour créer le superadmin avec toutes les permissions

            // Seeders pour les données de base
            ExternalContactsSeeder::class, // Seeder pour les contacts externes
            MailSystemSeeder::class, // Seeder pour le système de courriers

            // Seeders pour les auteurs et adresses (ISAD(G) et ISAAR(CPF))
            AuthorTypeSeeder::class, // Seeder pour les types d'auteurs
            AddressTypeSeeder::class, // Seeder pour les types d'adresses

            // Seeders pour les thésaurus
            ThesaurusTypologieSeeder::class, // Seeder pour le thésaurus des typologies documentaires
            ThesaurusActionsAdministrativesSeeder::class, // Seeder pour le thésaurus des actions administratives

            // Seeders pour les données records
            RecordStatusSeeder::class, // Seeder pour les statuts des dossiers
            RecordLevelSeeder::class,  // Seeder pour les niveaux hiérarchiques
            RecordSupportSeeder::class, // Seeder pour les supports physiques

            // Seeders pour les outils de gestion
            SortSeeder::class, // Seeder pour les sorts finaux (E, T, C)

            // Seeders pour les organisations
            OrganisationSeeder::class, // Seeder pour les organisations

            // Seeders pour les workflows
            WorkflowSeeder::class, // Seeder pour les exemples de workflows

            // Seeders pour les tâches
            TaskCategorySeeder::class, // Seeder pour les catégories de tâches
            TaskSeeder::class, // Seeder pour les exemples de tâches
        ]);
    }
}
