<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Organisation;
use App\Models\OpacConfiguration;
use App\Models\Template;
use App\Models\PublicUser;
use App\Models\PublicPage;
use App\Models\PublicNews;
use App\Models\PublicEvent;
use App\Models\PublicEventRegistration;
use App\Models\PublicTemplate;
use App\Models\PublicRecord;
use App\Models\Record;

class OpacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Démarrage du seeding OPAC...');

        DB::transaction(function () {
            // 1. Récupérer une organisation existante ou créer une organisation de test
            $organisation = Organisation::first();
            if (!$organisation) {
                $this->command->warn('Aucune organisation trouvée, création d\'une organisation de test...');
                $organisation = Organisation::create([
                    'name' => 'Bibliothèque Municipale de Test',
                    'code' => 'BM_TEST',
                    'description' => 'Organisation de test pour l\'OPAC',
                    'address' => '123 Rue de la Culture',
                    'city' => 'Ville Test',
                    'postal_code' => '12345',
                    'country' => 'France',
                    'phone' => '+33 1 23 45 67 89',
                    'email' => 'contact@bibliotheque-test.fr',
                    'website' => 'https://bibliotheque-test.fr'
                ]);
            }

            $this->command->info("✅ Organisation utilisée: {$organisation->name}");

            // 2. Configuration OPAC
            $this->seedOpacConfigurations($organisation->id);

            // 3. Templates OPAC
            $this->seedOpacTemplates();

            // 4. Utilisateurs publics
            $publicUsers = $this->seedPublicUsers();

            // 5. Pages statiques
            $this->seedPublicPages();

            // 6. Actualités
            $this->seedPublicNews();

            // 7. Événements et calendrier
            $this->seedPublicEvents($publicUsers);

            // 8. Enregistrements publics (si des records existent)
            $this->seedPublicRecords();

            // 9. Templates publics personnalisés
            $this->seedPublicTemplates();
        });

        $this->command->info('🎉 Seeding OPAC terminé avec succès !');
    }

    /**
     * Seed OPAC configurations
     */
    private function seedOpacConfigurations($organisationId)
    {
        $this->command->info('📋 Création des configurations OPAC...');

        $configurations = $this->getOpacConfigurations();

        foreach ($configurations as $config) {
            OpacConfiguration::updateOrCreate(
                [
                    'organisation_id' => $organisationId,
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

        $this->command->info('✅ Configurations OPAC créées');
    }

    /**
     * Get OPAC configuration data
     */
    private function getOpacConfigurations()
    {
        $standardHours = '09:00-18:00';

        return [
            // Configurations générales
            [
                'config_key' => 'opac_title',
                'config_value' => 'Catalogue en ligne - Bibliothèque Municipale',
                'config_type' => 'string',
                'description' => 'Titre principal de l\'OPAC'
            ],
            [
                'config_key' => 'opac_description',
                'config_value' => 'Découvrez nos collections et services en ligne',
                'config_type' => 'string',
                'description' => 'Description de l\'OPAC'
            ],
            [
                'config_key' => 'theme',
                'config_value' => 'modern-academic',
                'config_type' => 'string',
                'description' => 'Thème actif de l\'OPAC'
            ],
            [
                'config_key' => 'primary_color',
                'config_value' => '#2563eb',
                'config_type' => 'string',
                'description' => 'Couleur principale du thème'
            ],
            [
                'config_key' => 'secondary_color',
                'config_value' => '#64748b',
                'config_type' => 'string',
                'description' => 'Couleur secondaire du thème'
            ],
            [
                'config_key' => 'records_per_page',
                'config_value' => 20,
                'config_type' => 'integer',
                'description' => 'Nombre de résultats par page'
            ],
            [
                'config_key' => 'enable_advanced_search',
                'config_value' => true,
                'config_type' => 'boolean',
                'description' => 'Activer la recherche avancée'
            ],
            [
                'config_key' => 'show_statistics',
                'config_value' => true,
                'config_type' => 'boolean',
                'description' => 'Afficher les statistiques'
            ],
            [
                'config_key' => 'allow_guest_search',
                'config_value' => true,
                'config_type' => 'boolean',
                'description' => 'Autoriser la recherche pour les visiteurs'
            ],
            [
                'config_key' => 'contact_email',
                'config_value' => 'contact@bibliotheque-test.fr',
                'config_type' => 'string',
                'description' => 'Email de contact'
            ],
            [
                'config_key' => 'opening_hours',
                'config_value' => [
                    'lundi' => $standardHours,
                    'mardi' => $standardHours,
                    'mercredi' => $standardHours,
                    'jeudi' => '09:00-20:00',
                    'vendredi' => $standardHours,
                    'samedi' => '09:00-17:00',
                    'dimanche' => 'Fermé'
                ],
                'config_type' => 'array',
                'description' => 'Horaires d\'ouverture'
            ]
        ];
    }

    /**
     * Seed OPAC templates
     */
    private function seedOpacTemplates()
    {
        $this->command->info('🎨 Création des templates OPAC...');

        // Vérifier si les templates existent déjà
        if (Template::where('type', 'opac')->count() > 0) {
            $this->command->info('⚠️  Templates OPAC déjà présents, passage...');
            return;
        }

        // Les templates seront créés par OpacTemplateSeeder
        // Nous nous contentons de vérifier qu'ils existent
        $this->call(OpacTemplateSeeder::class);

        $this->command->info('✅ Templates OPAC vérifiés');
    }

    /**
     * Seed public users (utilisateurs OPAC)
     */
    private function seedPublicUsers()
    {
        $this->command->info('👥 Création des utilisateurs publics...');

        $users = [];

        // Utilisateur test approuvé
        $users[] = PublicUser::updateOrCreate(
            ['email' => 'marie.dupont@email.com'],
            [
                'name' => 'Dupont',
                'first_name' => 'Marie',
                'phone1' => '+33 6 12 34 56 78',
                'phone2' => '',
                'address' => '45 Avenue des Roses, 75001 Paris',
                'password' => Hash::make('password123'),
                'is_approved' => true,
                'email_verified_at' => now(),
                'preferences' => [
                    'language' => 'fr',
                    'notifications' => ['email', 'sms'],
                    'preferred_format' => 'pdf',
                    'search_history' => true
                ]
            ]
        );

        // Utilisateur en attente d'approbation
        $users[] = PublicUser::updateOrCreate(
            ['email' => 'jean.martin@email.com'],
            [
                'name' => 'Martin',
                'first_name' => 'Jean',
                'phone1' => '+33 6 98 76 54 32',
                'phone2' => '+33 1 45 67 89 01',
                'address' => '123 Rue de la Paix, 69000 Lyon',
                'password' => Hash::make('password456'),
                'is_approved' => false,
                'email_verified_at' => null,
                'preferences' => [
                    'language' => 'fr',
                    'notifications' => ['email'],
                    'preferred_format' => 'epub'
                ]
            ]
        );

        // Utilisateur actif avec historique
        $users[] = PublicUser::updateOrCreate(
            ['email' => 'sophie.bernard@email.com'],
            [
                'name' => 'Bernard',
                'first_name' => 'Sophie',
                'phone1' => '+33 7 11 22 33 44',
                'phone2' => '',
                'address' => '789 Boulevard des Sciences, 33000 Bordeaux',
                'password' => Hash::make('password789'),
                'is_approved' => true,
                'email_verified_at' => now(),
                'preferences' => [
                    'language' => 'fr',
                    'notifications' => ['email'],
                    'preferred_format' => 'pdf',
                    'search_history' => true,
                    'favorite_topics' => ['Histoire', 'Sciences', 'Littérature']
                ]
            ]
        );

        // Utilisateur étudiant
        $users[] = PublicUser::updateOrCreate(
            ['email' => 'lucas.petit@student.email.com'],
            [
                'name' => 'Petit',
                'first_name' => 'Lucas',
                'phone1' => '+33 6 55 44 33 22',
                'phone2' => '',
                'address' => 'Résidence Universitaire, 67000 Strasbourg',
                'password' => Hash::make('student123'),
                'is_approved' => true,
                'email_verified_at' => now(),
                'preferences' => [
                    'language' => 'fr',
                    'notifications' => ['email'],
                    'preferred_format' => 'digital',
                    'search_history' => true,
                    'student' => true,
                    'favorite_topics' => ['Informatique', 'Mathématiques', 'Physique']
                ]
            ]
        );

        // Utilisateur chercheur
        $users[] = PublicUser::updateOrCreate(
            ['email' => 'dr.claire.rousseau@research.fr'],
            [
                'name' => 'Rousseau',
                'first_name' => 'Claire',
                'phone1' => '+33 6 77 88 99 00',
                'phone2' => '+33 4 56 78 90 12',
                'address' => '456 Rue de la Recherche, 31000 Toulouse',
                'password' => Hash::make('research456'),
                'is_approved' => true,
                'email_verified_at' => now(),
                'preferences' => [
                    'language' => 'fr',
                    'notifications' => ['email'],
                    'preferred_format' => 'pdf',
                    'search_history' => true,
                    'researcher' => true,
                    'favorite_topics' => ['Médecine', 'Biologie', 'Recherche Médicale'],
                    'advanced_search_default' => true
                ]
            ]
        );

        $this->command->info('✅ ' . count($users) . ' utilisateurs publics créés');
        return $users;
    }

    /**
     * Seed public pages (pages statiques de l'OPAC)
     */
    private function seedPublicPages()
    {
        $this->command->info('📄 Création des pages statiques...');

        $admin = User::first();
        if (!$admin) {
            $this->command->warn('⚠️  Aucun utilisateur admin trouvé pour les pages');
            return;
        }

        // Page d'accueil
        PublicPage::updateOrCreate(
            ['slug' => 'accueil'],
            [
                'title' => 'Bienvenue à la Bibliothèque',
                'name' => 'Accueil',
                'content' => $this->getHomePageContent(),
                'meta_description' => 'Découvrez nos collections, services et ressources en ligne',
                'meta_keywords' => 'bibliothèque, catalogue, livres, ressources, culture',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 1,
                'parent_id' => null,
                'is_published' => true,
                'featured_image_path' => '/images/library-homepage.jpg'
            ]
        );

        // Page À propos
        PublicPage::updateOrCreate(
            ['slug' => 'a-propos'],
            [
                'title' => 'À propos de la bibliothèque',
                'name' => 'À propos',
                'content' => $this->getAboutPageContent(),
                'meta_description' => 'Histoire, mission et équipe de notre bibliothèque',
                'meta_keywords' => 'histoire, mission, équipe, bibliothèque',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 2,
                'parent_id' => null,
                'is_published' => true
            ]
        );

        // Page Services
        $servicesPage = PublicPage::updateOrCreate(
            ['slug' => 'services'],
            [
                'title' => 'Nos services',
                'name' => 'Services',
                'content' => $this->getServicesPageContent(),
                'meta_description' => 'Découvrez tous les services offerts par notre bibliothèque',
                'meta_keywords' => 'services, prêt, consultation, formation, aide',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 3,
                'parent_id' => null,
                'is_published' => true
            ]
        );

        // Sous-pages de Services
        PublicPage::updateOrCreate(
            ['slug' => 'pret-documents'],
            [
                'title' => 'Prêt de documents',
                'name' => 'Prêt de documents',
                'content' => $this->getLoanServiceContent(),
                'meta_description' => 'Conditions et modalités de prêt des documents',
                'meta_keywords' => 'prêt, documents, livres, conditions',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 1,
                'parent_id' => $servicesPage->id,
                'is_published' => true
            ]
        );

        PublicPage::updateOrCreate(
            ['slug' => 'formations-ateliers'],
            [
                'title' => 'Formations et ateliers',
                'name' => 'Formations et ateliers',
                'content' => $this->getTrainingPageContent(),
                'meta_description' => 'Formations et ateliers proposés par la bibliothèque',
                'meta_keywords' => 'formations, ateliers, numérique, recherche',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 2,
                'parent_id' => $servicesPage->id,
                'is_published' => true
            ]
        );

        // Page Contact
        PublicPage::updateOrCreate(
            ['slug' => 'contact'],
            [
                'title' => 'Nous contacter',
                'name' => 'Contact',
                'content' => $this->getContactPageContent(),
                'meta_description' => 'Coordonnées et horaires de la bibliothèque',
                'meta_keywords' => 'contact, adresse, téléphone, horaires',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 4,
                'parent_id' => null,
                'is_published' => true
            ]
        );

        // Page Aide
        PublicPage::updateOrCreate(
            ['slug' => 'aide'],
            [
                'title' => 'Aide et FAQ',
                'name' => 'Aide',
                'content' => $this->getHelpPageContent(),
                'meta_description' => 'Questions fréquentes et aide pour utiliser le catalogue',
                'meta_keywords' => 'aide, FAQ, questions, recherche, catalogue',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 5,
                'parent_id' => null,
                'is_published' => true
            ]
        );

        // Page Mentions légales
        PublicPage::updateOrCreate(
            ['slug' => 'mentions-legales'],
            [
                'title' => 'Mentions légales',
                'name' => 'Mentions légales',
                'content' => $this->getLegalPageContent(),
                'meta_description' => 'Mentions légales et politique de confidentialité',
                'meta_keywords' => 'mentions légales, confidentialité, RGPD',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 6,
                'parent_id' => null,
                'is_published' => true
            ]
        );

        $this->command->info('✅ Pages statiques créées');
    }

    /**
     * Seed public news (actualités)
     */
    private function seedPublicNews()
    {
        $this->command->info('📰 Création des actualités...');

        $admin = User::first();
        if (!$admin) {
            $this->command->warn('⚠️  Aucun utilisateur admin trouvé pour les actualités');
            return;
        }

        $newsItems = [
            [
                'title' => 'Nouvelle exposition : "L\'Art à travers les siècles"',
                'slug' => 'nouvelle-exposition-art-siecles',
                'summary' => 'Découvrez notre nouvelle exposition permanente dédiée à l\'art occidental',
                'content' => $this->getArtExhibitionNewsContent(),
                'image_path' => '/images/news/exposition-art.jpg',
                'published_at' => now()->subDays(5),
                'status' => 'published',
                'featured' => true
            ],
            [
                'title' => 'Acquisition de 500 nouveaux ouvrages de sciences',
                'slug' => 'acquisition-500-ouvrages-sciences',
                'summary' => 'Notre collection scientifique s\'enrichit de 500 nouveaux titres',
                'content' => $this->getScienceBooksNewsContent(),
                'image_path' => '/images/news/livres-sciences.jpg',
                'published_at' => now()->subDays(10),
                'status' => 'published',
                'featured' => false
            ],
            [
                'title' => 'Ateliers numériques : inscriptions ouvertes',
                'slug' => 'ateliers-numeriques-inscriptions-ouvertes',
                'summary' => 'Apprenez à maîtriser les outils numériques avec nos ateliers gratuits',
                'content' => $this->getDigitalWorkshopsNewsContent(),
                'image_path' => '/images/news/ateliers-numeriques.jpg',
                'published_at' => now()->subDays(3),
                'status' => 'published',
                'featured' => true
            ],
            [
                'title' => 'Extension des horaires d\'ouverture',
                'slug' => 'extension-horaires-ouverture',
                'summary' => 'À partir du 1er décembre, nouveaux horaires étendus',
                'content' => $this->getExtendedHoursNewsContent(),
                'image_path' => '/images/news/horaires.jpg',
                'published_at' => now()->subDays(15),
                'status' => 'published',
                'featured' => false
            ],
            [
                'title' => 'Concours de nouvelles 2024 : participez !',
                'slug' => 'concours-nouvelles-2024',
                'summary' => 'Le concours annuel de nouvelles est ouvert à tous les résidents',
                'content' => $this->getWritingContestNewsContent(),
                'image_path' => '/images/news/concours-nouvelles.jpg',
                'published_at' => now()->subDays(7),
                'status' => 'published',
                'featured' => true
            ]
        ];

        foreach ($newsItems as $newsData) {
            PublicNews::updateOrCreate(
                ['slug' => $newsData['slug']],
                array_merge($newsData, [
                    'name' => $newsData['title'], // Le champ name est obligatoire
                    'author_id' => $admin->id,
                    'is_published' => true, // Publier les actualités
                    'status' => 'published'
                ])
            );
        }

        $this->command->info('✅ ' . count($newsItems) . ' actualités créées');
    }

    /**
     * Seed public events (événements et calendrier)
     */
    private function seedPublicEvents($publicUsers)
    {
        $this->command->info('📅 Création des événements...');

        $events = [];

        // Conférence à venir
        $events[] = PublicEvent::updateOrCreate(
            ['name' => 'Conférence : "L\'Intelligence Artificielle et l\'Éducation"'],
            [
                'description' => $this->getAIConferenceEventContent(),
                'start_date' => now()->addDays(10)->setHour(18)->setMinute(30),
                'end_date' => now()->addDays(10)->setHour(20)->setMinute(0),
                'location' => 'Auditorium de la bibliothèque - 1er étage',
                'is_online' => false,
                'online_link' => null
            ]
        );

        // Atelier numérique en ligne
        $events[] = PublicEvent::updateOrCreate(
            ['name' => 'Atelier : Recherche documentaire avancée'],
            [
                'description' => $this->getAdvancedSearchWorkshopContent(),
                'start_date' => now()->addDays(5)->setHour(14)->setMinute(0),
                'end_date' => now()->addDays(5)->setHour(16)->setMinute(0),
                'location' => null,
                'is_online' => true,
                'online_link' => 'https://meet.bibliotheque-test.fr/atelier-recherche'
            ]
        );

        // Club de lecture mensuel
        $events[] = PublicEvent::updateOrCreate(
            ['name' => 'Club de lecture - "La littérature contemporaine"'],
            [
                'description' => $this->getBookClubEventContent(),
                'start_date' => now()->addDays(15)->setHour(19)->setMinute(0),
                'end_date' => now()->addDays(15)->setHour(21)->setMinute(0),
                'location' => 'Salon de lecture - Rez-de-chaussée',
                'is_online' => false,
                'online_link' => null
            ]
        );

        // Exposition temporaire
        $events[] = PublicEvent::updateOrCreate(
            ['name' => 'Exposition : "Photographies du patrimoine local"'],
            [
                'description' => $this->getPhotographyExhibitionContent(),
                'start_date' => now()->addDays(20)->setHour(9)->setMinute(0),
                'end_date' => now()->addDays(50)->setHour(18)->setMinute(0),
                'location' => 'Galerie d\'exposition - 2ème étage',
                'is_online' => false,
                'online_link' => null
            ]
        );

        // Formation aux outils numériques
        $events[] = PublicEvent::updateOrCreate(
            ['name' => 'Formation : "Maîtriser les tablettes et smartphones"'],
            [
                'description' => $this->getDigitalTrainingEventContent(),
                'start_date' => now()->addDays(12)->setHour(10)->setMinute(0),
                'end_date' => now()->addDays(12)->setHour(12)->setMinute(0),
                'location' => 'Salle informatique - 1er étage',
                'is_online' => false,
                'online_link' => null
            ]
        );

        // Événement passé pour l'historique
        $events[] = PublicEvent::updateOrCreate(
            ['name' => 'Journée Portes Ouvertes - Édition 2024'],
            [
                'description' => $this->getOpenDayEventContent(),
                'start_date' => now()->subDays(30)->setHour(9)->setMinute(0),
                'end_date' => now()->subDays(30)->setHour(17)->setMinute(0),
                'location' => 'Ensemble de la bibliothèque',
                'is_online' => false,
                'online_link' => null
            ]
        );

        // Créer quelques inscriptions d'exemple
        $this->seedEventRegistrations($events, $publicUsers);

        $this->command->info('✅ ' . count($events) . ' événements créés');
    }

    /**
     * Seed event registrations
     */
    private function seedEventRegistrations($events, $publicUsers)
    {
        if (empty($publicUsers) || empty($events)) {
            return;
        }

        $this->command->info('📝 Création des inscriptions aux événements...');

        $registrationCount = 0;

        // Créons quelques inscriptions d'exemple pour les événements qui ont des participants
        foreach ($events as $event) {
            // Simuler des inscriptions pour certains événements
            if (in_array($event->name, [
                'Conférence : "L\'Intelligence Artificielle et l\'Éducation"',
                'Atelier : Recherche documentaire avancée',
                'Club de lecture - "La littérature contemporaine"'
            ])) {
                                // Inscrire 2-3 utilisateurs par événement
                $usersToRegister = collect($publicUsers)->where('is_approved', true)->random(min(2, count($publicUsers)));

                foreach ($usersToRegister as $user) {
                    try {
                        PublicEventRegistration::updateOrCreate(
                            [
                                'event_id' => $event->id,
                                'user_id' => $user->id
                            ],
                            [
                                'status' => collect(['registered', 'confirmed'])->random(),
                                'registered_at' => now()->subDays(rand(1, 5)),
                                'notes' => 'Inscription via OPAC'
                            ]
                        );
                        $registrationCount++;
                    } catch (\Exception $e) {
                        // Ignore si la table n'existe pas ou a une structure différente
                        $this->command->warn("Impossible de créer les inscriptions: " . $e->getMessage());
                        break 2; // Sortir des deux boucles
                    }
                }
            }
        }        $this->command->info("✅ {$registrationCount} inscriptions créées");
    }

    /**
     * Seed public records (documents publics)
     */
    private function seedPublicRecords()
    {
        $this->command->info('📚 Création des enregistrements publics...');

        // Vérifier s'il y a des records existants
        $existingRecords = Record::limit(10)->get();

        if ($existingRecords->isEmpty()) {
            $this->command->warn('⚠️  Aucun enregistrement trouvé, création ignorée');
            return;
        }

        $admin = User::first();
        $recordsPublished = 0;

        foreach ($existingRecords as $record) {
            // Publier aléatoirement certains enregistrements
            if (rand(1, 3) === 1) { // 1 chance sur 3
                PublicRecord::updateOrCreate(
                    ['record_id' => $record->id],
                    [
                        'published_at' => now()->subDays(rand(1, 90)),
                        'expires_at' => null, // Pas d'expiration par défaut
                        'published_by' => $admin->id,
                        'publication_notes' => 'Document publié automatiquement via seeder OPAC'
                    ]
                );
                $recordsPublished++;
            }
        }

        $this->command->info("✅ {$recordsPublished} enregistrements publiés sur l'OPAC");
    }

    /**
     * Seed public templates
     */
    private function seedPublicTemplates()
    {
        $this->command->info('🎨 Création des templates publics...');

        $templates = [
            [
                'name' => 'Template Recherche Avancée',
                'description' => 'Template personnalisé pour la recherche avancée',
                'type' => 'page',
                'content' => '<div class="advanced-search"><h2>{{title}}</h2><p>{{subtitle}}</p><form><input placeholder="{{placeholder}}" /></form></div>',
                'status' => 'active',
                'parameters' => [
                    'show_filters' => true,
                    'show_sort_options' => true,
                    'default_sort' => 'relevance',
                    'filters' => ['type', 'author', 'year', 'subject']
                ],
                'values' => [
                    'title' => 'Recherche Avancée',
                    'subtitle' => 'Affinez votre recherche',
                    'placeholder' => 'Saisissez vos termes de recherche...'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Template Accueil OPAC',
                'description' => 'Template pour la page d\'accueil de l\'OPAC',
                'type' => 'page',
                'content' => '<div class="opac-home"><h1>{{welcome_title}}</h1><p>{{welcome_message}}</p><section class="featured">{{featured_title}}</section></div>',
                'status' => 'active',
                'parameters' => [
                    'show_welcome_message' => true,
                    'show_featured_collections' => true,
                    'show_recent_additions' => true,
                    'show_statistics' => true,
                    'show_news' => true,
                    'news_limit' => 5
                ],
                'values' => [
                    'welcome_title' => 'Bienvenue sur notre catalogue',
                    'welcome_message' => 'Explorez nos collections et découvrez de nouvelles ressources',
                    'featured_title' => 'Collections mises en avant',
                    'recent_title' => 'Nouvelles acquisitions'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Template Liste Résultats',
                'description' => 'Template d\'affichage des résultats de recherche',
                'type' => 'page',
                'content' => '<div class="results"><h2>Résultats</h2><div class="count">{{results_count_format}}</div><div class="items"><!-- results --></div></div>',
                'status' => 'active',
                'parameters' => [
                    'view_mode' => 'list', // list ou grid
                    'show_thumbnails' => true,
                    'show_summary' => true,
                    'show_availability' => true,
                    'items_per_page' => 20
                ],
                'values' => [
                    'no_results_message' => 'Aucun résultat trouvé pour votre recherche',
                    'results_count_format' => '{count} résultat(s) trouvé(s)',
                    'loading_message' => 'Recherche en cours...'
                ],
                'is_active' => true
            ]
        ];

        foreach ($templates as $templateData) {
            PublicTemplate::updateOrCreate(
                ['name' => $templateData['name']],
                $templateData
            );
        }

        $this->command->info('✅ ' . count($templates) . ' templates publics créés');
    }

    // Méthodes pour le contenu des pages
    private function getHomePageContent()
    {
        return '<div class="welcome-section">
            <h1>Bienvenue à la Bibliothèque Municipale</h1>
            <p class="lead">Découvrez nos collections riches et variées, nos services innovants et nos espaces de travail modernes. Notre bibliothèque est un lieu de savoir, de culture et d\'échange pour tous.</p>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <h3>🔍 Rechercher</h3>
                        <p>Explorez notre catalogue de plus de 50 000 documents : livres, revues, documents numériques, DVD...</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <h3>📚 Emprunter</h3>
                        <p>Empruntez jusqu\'à 10 documents pour 3 semaines. Prolongez vos prêts facilement en ligne.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <h3>🎓 Apprendre</h3>
                        <p>Participez à nos ateliers, formations et événements culturels tout au long de l\'année.</p>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getAboutPageContent()
    {
        return '<h1>À propos de notre bibliothèque</h1>

        <h2>Notre histoire</h2>
        <p>Créée en 1950, la Bibliothèque Municipale est devenue au fil des décennies un pilier culturel de notre ville. Rénovée en 2015, elle allie patrimoine architectural et modernité technologique.</p>

        <h2>Notre mission</h2>
        <p>Nous nous engageons à :</p>
        <ul>
            <li>Favoriser l\'accès à l\'information et à la culture pour tous</li>
            <li>Accompagner les usagers dans leurs recherches et projets</li>
            <li>Promouvoir la lecture et les pratiques culturelles</li>
            <li>Soutenir l\'éducation et la formation tout au long de la vie</li>
        </ul>

        <h2>Notre équipe</h2>
        <p>Une équipe de 12 professionnels passionnés vous accueille et vous conseille : bibliothécaires, médiathécaires, animateurs culturels et personnel d\'accueil.</p>';
    }

    private function getServicesPageContent()
    {
        return '<h1>Nos services</h1>

        <div class="services-grid">
            <div class="service-item">
                <h3>📖 Prêt et consultation</h3>
                <p>Empruntez ou consultez sur place nos documents. Accès libre à tous nos espaces de lecture.</p>
            </div>

            <div class="service-item">
                <h3>💻 Accès numérique</h3>
                <p>Wifi gratuit, postes informatiques en libre accès, ressources numériques en ligne.</p>
            </div>

            <div class="service-item">
                <h3>👥 Aide personnalisée</h3>
                <p>Accompagnement dans vos recherches documentaires et formations aux outils numériques.</p>
            </div>

            <div class="service-item">
                <h3>🎭 Animation culturelle</h3>
                <p>Conférences, expositions, clubs de lecture, ateliers créatifs pour tous les âges.</p>
            </div>
        </div>';
    }

    private function getLoanServiceContent()
    {
        return '<h1>Prêt de documents</h1>

        <h2>Conditions de prêt</h2>
        <ul>
            <li><strong>Nombre de documents :</strong> 10 maximum par carte</li>
            <li><strong>Durée :</strong> 3 semaines (21 jours)</li>
            <li><strong>Prolongation :</strong> 2 prolongations possibles si le document n\'est pas réservé</li>
            <li><strong>Réservation :</strong> 5 réservations maximum par carte</li>
        </ul>

        <h2>Tarifs</h2>
        <ul>
            <li>Résidents de la ville : Gratuit</li>
            <li>Extérieurs : 25€ par an</li>
            <li>Étudiants/demandeurs d\'emploi : 10€ par an</li>
        </ul>';
    }

    private function getTrainingPageContent()
    {
        return '<h1>Formations et ateliers</h1>

        <h2>Formations numériques</h2>
        <ul>
            <li>Initiation à l\'informatique</li>
            <li>Internet et navigation web</li>
            <li>Messagerie électronique</li>
            <li>Démarches administratives en ligne</li>
        </ul>

        <h2>Ateliers créatifs</h2>
        <ul>
            <li>Écriture créative</li>
            <li>Généalogie</li>
            <li>Retouche photo</li>
            <li>Création de blogs</li>
        </ul>

        <p><strong>Inscription obligatoire</strong> - Places limitées</p>';
    }

    private function getContactPageContent()
    {
        return '<h1>Nous contacter</h1>

        <div class="contact-info">
            <div class="contact-section">
                <h2>📍 Adresse</h2>
                <p>123 Rue de la Culture<br>
                12345 Ville Test<br>
                France</p>
            </div>

            <div class="contact-section">
                <h2>📞 Téléphone</h2>
                <p>+33 1 23 45 67 89</p>
            </div>

            <div class="contact-section">
                <h2>✉️ Email</h2>
                <p>contact@bibliotheque-test.fr</p>
            </div>

            <div class="contact-section">
                <h2>🕐 Horaires</h2>
                <ul>
                    <li>Lundi - Vendredi : 9h00 - 18h00</li>
                    <li>Jeudi : 9h00 - 20h00 (nocturne)</li>
                    <li>Samedi : 9h00 - 17h00</li>
                    <li>Dimanche : Fermé</li>
                </ul>
            </div>
        </div>';
    }

    private function getHelpPageContent()
    {
        return '<h1>Aide et FAQ</h1>

        <div class="faq-section">
            <h2>❓ Questions fréquentes</h2>

            <div class="faq-item">
                <h3>Comment rechercher un document ?</h3>
                <p>Utilisez le moteur de recherche en saisissant le titre, l\'auteur ou des mots-clés. Vous pouvez également utiliser la recherche avancée pour affiner vos critères.</p>
            </div>

            <div class="faq-item">
                <h3>Comment prolonger mes prêts ?</h3>
                <p>Connectez-vous à votre compte et accédez à la section "Mes prêts". Cliquez sur "Prolonger" à côté du document souhaité.</p>
            </div>

            <div class="faq-item">
                <h3>Comment réserver un document ?</h3>
                <p>Sur la page du document, cliquez sur "Réserver". Vous serez averti par email dès que le document sera disponible.</p>
            </div>
        </div>';
    }

    private function getLegalPageContent()
    {
        return '<h1>Mentions légales</h1>

        <h2>Éditeur du site</h2>
        <p>Bibliothèque Municipale de Test<br>
        123 Rue de la Culture, 12345 Ville Test<br>
        Téléphone : +33 1 23 45 67 89<br>
        Email : contact@bibliotheque-test.fr</p>

        <h2>Hébergement</h2>
        <p>Ce site est hébergé par notre fournisseur de services cloud.</p>

        <h2>Protection des données personnelles</h2>
        <p>Conformément au RGPD, nous nous engageons à protéger vos données personnelles. Pour toute question, contactez notre DPO à l\'adresse : dpo@bibliotheque-test.fr</p>';
    }

    // Méthodes pour le contenu des actualités
    private function getArtExhibitionNewsContent()
    {
        return '<p>Nous sommes ravis de vous présenter notre nouvelle exposition permanente <strong>"L\'Art à travers les siècles"</strong>.</p>

        <p>Cette exposition retrace l\'évolution de l\'art occidental du Moyen Âge à nos jours à travers une sélection d\'œuvres reproduites et de documents d\'archives exceptionnels.</p>

        <h3>Au programme :</h3>
        <ul>
            <li>Art médiéval et renaissance</li>
            <li>Les grands maîtres classiques</li>
            <li>L\'art moderne et contemporain</li>
            <li>Ateliers découverte pour enfants</li>
        </ul>

        <p><strong>Entrée libre</strong> - Du lundi au samedi aux heures d\'ouverture de la bibliothèque.</p>';
    }

    private function getScienceBooksNewsContent()
    {
        return '<p>Bonne nouvelle pour les passionnés de sciences ! Notre collection s\'enrichit de <strong>500 nouveaux ouvrages</strong> dans tous les domaines scientifiques.</p>

        <h3>Nouveautés par discipline :</h3>
        <ul>
            <li>Physique et astronomie : 120 ouvrages</li>
            <li>Biologie et médecine : 150 ouvrages</li>
            <li>Mathématiques et informatique : 100 ouvrages</li>
            <li>Sciences de la terre : 80 ouvrages</li>
            <li>Chimie : 50 ouvrages</li>
        </ul>

        <p>Ces acquisitions incluent les dernières parutions de 2024, des manuels universitaires et des ouvrages de vulgarisation scientifique.</p>';
    }

    private function getDigitalWorkshopsNewsContent()
    {
        return '<p>Développez vos compétences numériques avec nos <strong>ateliers gratuits</strong> !</p>

        <h3>Prochaines sessions :</h3>
        <ul>
            <li><strong>15 décembre :</strong> Initiation aux tablettes (14h-16h)</li>
            <li><strong>18 décembre :</strong> Démarches administratives en ligne (10h-12h)</li>
            <li><strong>22 décembre :</strong> Créer et gérer ses mots de passe (14h-15h30)</li>
        </ul>

        <p><strong>Inscription obligatoire</strong> au bureau d\'accueil ou par téléphone. Places limitées à 8 participants par atelier.</p>';
    }

    private function getExtendedHoursNewsContent()
    {
        return '<p>Pour mieux vous servir, nous étendons nos horaires d\'ouverture à partir du <strong>1er décembre 2024</strong>.</p>

        <h3>Nouveaux horaires :</h3>
        <ul>
            <li><strong>Lundi à mercredi :</strong> 9h00 - 19h00 (au lieu de 18h00)</li>
            <li><strong>Jeudi :</strong> 9h00 - 21h00 (nocturne étendue)</li>
            <li><strong>Vendredi :</strong> 9h00 - 19h00</li>
            <li><strong>Samedi :</strong> 9h00 - 18h00 (au lieu de 17h00)</li>
        </ul>

        <p>Cette extension répond à vos demandes pour plus de flexibilité dans vos visites.</p>';
    }

    private function getWritingContestNewsContent()
    {
        return '<p>Le <strong>concours de nouvelles 2024</strong> est lancé ! Thème cette année : "Voyages extraordinaires".</p>

        <h3>Modalités :</h3>
        <ul>
            <li><strong>Public :</strong> Tous les résidents de plus de 16 ans</li>
            <li><strong>Format :</strong> Nouvelle de 5 à 15 pages</li>
            <li><strong>Date limite :</strong> 31 janvier 2025</li>
            <li><strong>Remise des prix :</strong> 15 mars 2025</li>
        </ul>

        <h3>Prix :</h3>
        <ul>
            <li>1er prix : 500€ + publication</li>
            <li>2e prix : 300€</li>
            <li>3e prix : 200€</li>
            <li>Prix coup de cœur du public : 100€</li>
        </ul>

        <p>Règlement complet disponible à l\'accueil et sur notre site.</p>';
    }

    // Méthodes pour le contenu des événements
    private function getAIConferenceEventContent()
    {
        return 'Conférence exceptionnelle animée par le Dr. Sarah Martinez, spécialiste en IA éducative.

        Au programme :
        - Impact de l\'IA sur les méthodes d\'apprentissage
        - Outils d\'IA pour l\'éducation
        - Enjeux éthiques et perspectives d\'avenir
        - Session de questions-réponses

        Entrée libre sur inscription. Cocktail offert à l\'issue de la conférence.';
    }

    private function getAdvancedSearchWorkshopContent()
    {
        return 'Atelier pratique en ligne pour maîtriser les techniques de recherche documentaire avancée.

        Vous apprendrez à :
        - Utiliser les opérateurs booléens
        - Exploiter les bases de données spécialisées
        - Évaluer la fiabilité des sources
        - Organiser votre veille informationnelle

        Matériel requis : ordinateur avec connexion internet stable.';
    }

    private function getBookClubEventContent()
    {
        return 'Rencontre mensuelle de notre club de lecture dédiée à la littérature contemporaine française.

        Ce mois-ci, nous discuterons de :
        - "Yoga" d\'Emmanuel Carrère
        - "Civilizations" de Laurent Binet
        - "L\'Anomalie" d\'Hervé Le Tellier

        Que vous ayez lu un ou tous ces livres, votre participation enrichira nos échanges !';
    }

    private function getPhotographyExhibitionContent()
    {
        return 'Exposition photographique présentant le riche patrimoine architectural et naturel de notre région.

        Découvrez :
        - 40 photographies d\'exception
        - Châteaux et monuments historiques
        - Paysages préservés
        - Rencontres avec les photographes les week-ends

        Exposition accessible pendant les horaires d\'ouverture. Visite guidée possible sur demande.';
    }

    private function getDigitalTrainingEventContent()
    {
        return 'Formation pratique aux tablettes et smartphones pour les débutants et les utilisateurs souhaitant se perfectionner.

        Programme :
        - Prise en main et navigation
        - Installation et gestion des applications
        - Sécurité et paramètres de confidentialité
        - Astuces et bonnes pratiques

        Matériel fourni. Apportez votre propre appareil si vous en avez un.';
    }

    private function getOpenDayEventContent()
    {
        return 'Journée portes ouvertes exceptionnelle avec de nombreuses animations pour découvrir tous nos services.

        Programme de la journée :
        - Visites guidées toutes les heures
        - Démonstrations des ressources numériques
        - Ateliers découverte pour enfants
        - Rencontre avec l\'équipe
        - Exposition des nouveautés
        - Buffet de l\'amitié

        Entrée libre, venez nombreux !';
    }
}
