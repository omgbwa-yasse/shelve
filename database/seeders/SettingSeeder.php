<?php

namespace Database\Seeders;

use App\Models\SettingCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les catégories principales
        $generalCategory = SettingCategory::create([
            'name' => 'Général',
            'description' => 'Paramètres généraux de l\'application',
        ]);

        $securityCategory = SettingCategory::create([
            'name' => 'Sécurité',
            'description' => 'Paramètres de sécurité et d\'authentification',
        ]);

        $notificationCategory = SettingCategory::create([
            'name' => 'Notifications',
            'description' => 'Paramètres de notifications et d\'alertes',
        ]);

        // Sous-catégorie pour les notifications
        $emailNotifCategory = SettingCategory::create([
            'name' => 'Email',
            'description' => 'Paramètres de notifications par email',
            'parent_id' => $notificationCategory->id,
        ]);

        // Créer quelques paramètres d'exemple
        Setting::create([
            'category_id' => $generalCategory->id,
            'name' => 'app_name',
            'type' => 'string',
            'default_value' => json_encode('Shelves'),
            'description' => 'Nom de l\'application',
            'is_system' => true,
            'constraints' => json_encode(['max_length' => 100]),
        ]);

        Setting::create([
            'category_id' => $generalCategory->id,
            'name' => 'maintenance_mode',
            'type' => 'boolean',
            'default_value' => json_encode(false),
            'description' => 'Mode maintenance activé',
            'is_system' => true,
        ]);

        Setting::create([
            'category_id' => $securityCategory->id,
            'name' => 'session_timeout',
            'type' => 'integer',
            'default_value' => json_encode(3600),
            'description' => 'Délai d\'expiration de session (en secondes)',
            'is_system' => true,
            'constraints' => json_encode(['min' => 300, 'max' => 86400]),
        ]);

        Setting::create([
            'category_id' => $securityCategory->id,
            'name' => 'password_min_length',
            'type' => 'integer',
            'default_value' => json_encode(8),
            'description' => 'Longueur minimale des mots de passe',
            'is_system' => true,
            'constraints' => json_encode(['min' => 6, 'max' => 50]),
        ]);

        Setting::create([
            'category_id' => $emailNotifCategory->id,
            'name' => 'email_notifications_enabled',
            'type' => 'boolean',
            'default_value' => json_encode(true),
            'description' => 'Activer les notifications par email',
            'is_system' => false,
        ]);

        Setting::create([
            'category_id' => $emailNotifCategory->id,
            'name' => 'email_frequency',
            'type' => 'string',
            'default_value' => json_encode('daily'),
            'description' => 'Fréquence des notifications email',
            'is_system' => false,
            'constraints' => json_encode(['options' => ['immediate', 'daily', 'weekly', 'never']]),
        ]);
    }
}
