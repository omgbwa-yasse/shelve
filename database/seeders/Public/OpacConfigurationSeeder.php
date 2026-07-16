<?php

namespace Database\Seeders\Public;

use App\Models\OpacConfiguration;
use App\Models\Organisation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OpacConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('🔧 Création des configurations OPAC par défaut...');

        // Récupérer toutes les organisations
        $organisations = Organisation::all();

        foreach ($organisations as $organisation) {
            $this->createDefaultConfigurationsForOrganisation($organisation);
        }

        $this->command->info('✅ Configurations OPAC créées avec succès pour ' . $organisations->count() . ' organisations');
    }

    private function createDefaultConfigurationsForOrganisation($organisation)
    {
        $configs = [
            [
                'config_key' => 'opac_title',
                'config_value' => 'Catalogue en ligne - ' . $organisation->name,
                'config_type' => 'string',
                'description' => 'Titre affiché sur l\'OPAC'
            ],
            [
                'config_key' => 'visible_organisations',
                'config_value' => [$organisation->id],
                'config_type' => 'array',
                'description' => 'Organisations visibles dans ce catalogue'
            ],
            [
                'config_key' => 'show_statistics',
                'config_value' => true,
                'config_type' => 'boolean',
                'description' => 'Afficher les statistiques sur la page d\'accueil'
            ],
            [
                'config_key' => 'show_recent_records',
                'config_value' => true,
                'config_type' => 'boolean',
                'description' => 'Afficher les documents récents'
            ],
            [
                'config_key' => 'allow_downloads',
                'config_value' => false,
                'config_type' => 'boolean',
                'description' => 'Autoriser le téléchargement des documents'
            ],
            [
                'config_key' => 'records_per_page',
                'config_value' => 15,
                'config_type' => 'integer',
                'description' => 'Nombre de résultats par page'
            ],
            [
                'config_key' => 'allowed_file_types',
                'config_value' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
                'config_type' => 'array',
                'description' => 'Types de fichiers autorisés en téléchargement'
            ],
            [
                'config_key' => 'show_full_record_details',
                'config_value' => true,
                'config_type' => 'boolean',
                'description' => 'Afficher tous les détails des documents'
            ],
            [
                'config_key' => 'show_attachments',
                'config_value' => false,
                'config_type' => 'boolean',
                'description' => 'Afficher les pièces jointes'
            ],
            [
                'config_key' => 'theme',
                'config_value' => 'default',
                'config_type' => 'string',
                'description' => 'Thème visuel de l\'OPAC'
            ],
            [
                'config_key' => 'primary_color',
                'config_value' => '#007bff',
                'config_type' => 'string',
                'description' => 'Couleur principale du thème'
            ],
            [
                'config_key' => 'secondary_color',
                'config_value' => '#6c757d',
                'config_type' => 'string',
                'description' => 'Couleur secondaire du thème'
            ],
            [
                'config_key' => 'logo_url',
                'config_value' => '',
                'config_type' => 'string',
                'description' => 'URL du logo de l\'organisation'
            ],
            [
                'config_key' => 'footer_text',
                'config_value' => 'Système de gestion documentaire - ' . $organisation->name,
                'config_type' => 'string',
                'description' => 'Texte affiché dans le pied de page'
            ],
            [
                'config_key' => 'contact_email',
                'config_value' => '',
                'config_type' => 'string',
                'description' => 'Email de contact affiché sur l\'OPAC'
            ],
            [
                'config_key' => 'help_url',
                'config_value' => '',
                'config_type' => 'string',
                'description' => 'URL de la page d\'aide'
            ]
        ];

        foreach ($configs as $config) {
            OpacConfiguration::updateOrCreate(
                [
                    'organisation_id' => $organisation->id,
                    'config_key' => $config['config_key']
                ],
                [
                    'config_value' => $config['config_value'],
                    'config_type' => $config['config_type'],
                    'description' => $config['description'],
                    'is_active' => true
                ]
            );
        }

        $this->command->info("  ✅ Configurations créées pour {$organisation->name}");
    }
}

