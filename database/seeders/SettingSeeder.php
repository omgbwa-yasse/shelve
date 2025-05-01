<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Setting categories
        $settingCategories = [
            [
                'id' => 1,
                'name' => 'Système',
                'description' => 'Paramètres système généraux',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Archivage',
                'description' => 'Paramètres liés à l\'archivage',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Communication',
                'description' => 'Paramètres liés aux communications',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Interface',
                'description' => 'Paramètres d\'interface utilisateur',
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Portail public',
                'description' => 'Paramètres du portail public',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Settings
        $settings = [
            [
                'id' => 1,
                'category_id' => 1,
                'name' => 'app_name',
                'type' => 'string',
                'default_value' => json_encode('GestArch'),
                'description' => 'Nom de l\'application',
                'is_system' => true,
                'constraints' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'name' => 'app_logo',
                'type' => 'string',
                'default_value' => json_encode('/img/logo.png'),
                'description' => 'Chemin du logo de l\'application',
                'is_system' => true,
                'constraints' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'category_id' => 1,
                'name' => 'backup_frequency',
                'type' => 'string',
                'default_value' => json_encode('daily'),
                'description' => 'Fréquence des sauvegardes automatiques',
                'is_system' => true,
                'constraints' => json_encode(['options' => ['hourly', 'daily', 'weekly', 'monthly']]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'category_id' => 2,
                'name' => 'default_retention_period',
                'type' => 'integer',
                'default_value' => json_encode(10),
                'description' => 'Durée de conservation par défaut (en années)',
                'is_system' => true,
                'constraints' => json_encode(['min' => 1, 'max' => 100]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'category_id' => 2,
                'name' => 'auto_archive_enabled',
                'type' => 'boolean',
                'default_value' => json_encode(true),
                'description' => 'Activer l\'archivage automatique',
                'is_system' => true,
                'constraints' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'category_id' => 3,
                'name' => 'default_communication_duration',
                'type' => 'integer',
                'default_value' => json_encode(15),
                'description' => 'Durée par défaut des communications (en jours)',
                'is_system' => true,
                'constraints' => json_encode(['min' => 1, 'max' => 365]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'category_id' => 3,
                'name' => 'max_documents_per_communication',
                'type' => 'integer',
                'default_value' => json_encode(10),
                'description' => 'Nombre maximum de documents par communication',
                'is_system' => true,
                'constraints' => json_encode(['min' => 1, 'max' => 50]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'category_id' => 4,
                'name' => 'theme',
                'type' => 'string',
                'default_value' => json_encode('light'),
                'description' => 'Thème de l\'interface',
                'is_system' => false,
                'constraints' => json_encode(['options' => ['light', 'dark', 'blue', 'green']]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 9,
                'category_id' => 4,
                'name' => 'items_per_page',
                'type' => 'integer',
                'default_value' => json_encode(20),
                'description' => 'Nombre d\'éléments par page',
                'is_system' => false,
                'constraints' => json_encode(['options' => [10, 20, 50, 100]]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 10,
                'category_id' => 5,
                'name' => 'public_portal_enabled',
                'type' => 'boolean',
                'default_value' => json_encode(true),
                'description' => 'Activer le portail public',
                'is_system' => true,
                'constraints' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 11,
                'category_id' => 5,
                'name' => 'require_account_approval',
                'type' => 'boolean',
                'default_value' => json_encode(true),
                'description' => 'Exiger l\'approbation des comptes utilisateurs',
                'is_system' => true,
                'constraints' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 12,
                'category_id' => 5,
                'name' => 'max_document_requests_per_user',
                'type' => 'integer',
                'default_value' => json_encode(5),
                'description' => 'Nombre maximum de demandes de documents par utilisateur (par mois)',
                'is_system' => true,
                'constraints' => json_encode(['min' => 1, 'max' => 50]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Setting values (global)
        $settingValues = [
            [
                'id' => 1,
                'setting_id' => 1,
                'user_id' => null,
                'organisation_id' => null,
                'value' => json_encode('GestArch - Système de Gestion des Archives'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'setting_id' => 3,
                'user_id' => null,
                'organisation_id' => null,
                'value' => json_encode('daily'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'setting_id' => 4,
                'user_id' => null,
                'organisation_id' => null,
                'value' => json_encode(15),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'setting_id' => 10,
                'user_id' => null,
                'organisation_id' => null,
                'value' => json_encode(true),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // User-specific setting values
        $userSettingValues = [
            [
                'id' => 5,
                'setting_id' => 8,
                'user_id' => 1,
                'organisation_id' => null,
                'value' => json_encode('dark'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'setting_id' => 9,
                'user_id' => 1,
                'organisation_id' => null,
                'value' => json_encode(50),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'setting_id' => 8,
                'user_id' => 5,
                'organisation_id' => null,
                'value' => json_encode('blue'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'setting_id' => 9,
                'user_id' => 9,
                'organisation_id' => null,
                'value' => json_encode(10),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('setting_categories')->insert($settingCategories);
        DB::table('settings')->insert($settings);
        DB::table('setting_values')->insert($settingValues);
        DB::table('setting_values')->insert($userSettingValues);
    }
}
