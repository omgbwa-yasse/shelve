<?php

namespace Database\Seeders\Public;

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
        $this->command->info('ðŸš€ DÃ©marrage du seeding OPAC...');

        DB::transaction(function () {
            // 1. RÃ©cupÃ©rer une organisation existante ou crÃ©er une organisation de test
            $organisation = Organisation::first();
            if (!$organisation) {
                $this->command->warn('Aucune organisation trouvÃ©e, crÃ©ation d\'une organisation de test...');
                $organisation = Organisation::create([
                    'name' => 'BibliothÃ¨que Municipale de Test',
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

            $this->command->info("âœ… Organisation utilisÃ©e: {$organisation->name}");

            // 2. Configuration OPAC
            $this->seedOpacConfigurations($organisation->id);

            // 3. Templates OPAC
            $this->seedOpacTemplates();

            // 4. Utilisateurs publics
            $publicUsers = $this->seedPublicUsers();

            // 5. Pages statiques
            $this->seedPublicPages();

            // 6. ActualitÃ©s
            $this->seedPublicNews();

            // 7. Ã‰vÃ©nements et calendrier
            $this->seedPublicEvents($publicUsers);

            // 8. Enregistrements publics (si des records existent)
            $this->seedPublicRecords();

            // 9. Templates publics personnalisÃ©s
            $this->seedPublicTemplates();
        });

        $this->command->info('ðŸŽ‰ Seeding OPAC terminÃ© avec succÃ¨s !');
    }

    /**
     * Seed OPAC configurations
     */
    private function seedOpacConfigurations($organisationId)
    {
        $this->command->info('ðŸ“‹ CrÃ©ation des configurations OPAC...');

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

        $this->command->info('âœ… Configurations OPAC crÃ©Ã©es');
    }

    /**
     * Get OPAC configuration data
     */
    private function getOpacConfigurations()
    {
        $standardHours = '09:00-18:00';

        return [
            // Configurations gÃ©nÃ©rales
            [
                'config_key' => 'opac_title',
                'config_value' => 'Catalogue en ligne - BibliothÃ¨que Municipale',
                'config_type' => 'string',
                'description' => 'Titre principal de l\'OPAC'
            ],
            [
                'config_key' => 'opac_description',
                'config_value' => 'DÃ©couvrez nos collections et services en ligne',
                'config_type' => 'string',
                'description' => 'Description de l\'OPAC'
            ],
            [
                'config_key' => 'theme',
                'config_value' => 'modern-academic',
                'config_type' => 'string',
                'description' => 'ThÃ¨me actif de l\'OPAC'
            ],
            [
                'config_key' => 'primary_color',
                'config_value' => '#2563eb',
                'config_type' => 'string',
                'description' => 'Couleur principale du thÃ¨me'
            ],
            [
                'config_key' => 'secondary_color',
                'config_value' => '#64748b',
                'config_type' => 'string',
                'description' => 'Couleur secondaire du thÃ¨me'
            ],
            [
                'config_key' => 'records_per_page',
                'config_value' => 20,
                'config_type' => 'integer',
                'description' => 'Nombre de rÃ©sultats par page'
            ],
            [
                'config_key' => 'enable_advanced_search',
                'config_value' => true,
                'config_type' => 'boolean',
                'description' => 'Activer la recherche avancÃ©e'
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
                    'dimanche' => 'FermÃ©'
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
        $this->command->info('ðŸŽ¨ CrÃ©ation des templates OPAC...');

        // VÃ©rifier si les templates existent dÃ©jÃ 
        if (Template::where('type', 'opac')->count() > 0) {
            $this->command->info('âš ï¸  Templates OPAC dÃ©jÃ  prÃ©sents, passage...');
            return;
        }

        // Les templates seront crÃ©Ã©s par OpacTemplateSeeder
        // Nous nous contentons de vÃ©rifier qu'ils existent
        $this->call(OpacTemplateSeeder::class);

        $this->command->info('âœ… Templates OPAC vÃ©rifiÃ©s');
    }

    /**
     * Seed public users (utilisateurs OPAC)
     */
    private function seedPublicUsers()
    {
        $this->command->info('ðŸ‘¥ CrÃ©ation des utilisateurs publics...');

        $users = [];

        // Utilisateur test approuvÃ©
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
                    'favorite_topics' => ['Histoire', 'Sciences', 'LittÃ©rature']
                ]
            ]
        );

        // Utilisateur Ã©tudiant
        $users[] = PublicUser::updateOrCreate(
            ['email' => 'lucas.petit@student.email.com'],
            [
                'name' => 'Petit',
                'first_name' => 'Lucas',
                'phone1' => '+33 6 55 44 33 22',
                'phone2' => '',
                'address' => 'RÃ©sidence Universitaire, 67000 Strasbourg',
                'password' => Hash::make('student123'),
                'is_approved' => true,
                'email_verified_at' => now(),
                'preferences' => [
                    'language' => 'fr',
                    'notifications' => ['email'],
                    'preferred_format' => 'digital',
                    'search_history' => true,
                    'student' => true,
                    'favorite_topics' => ['Informatique', 'MathÃ©matiques', 'Physique']
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
                    'favorite_topics' => ['MÃ©decine', 'Biologie', 'Recherche MÃ©dicale'],
                    'advanced_search_default' => true
                ]
            ]
        );

        $this->command->info('âœ… ' . count($users) . ' utilisateurs publics crÃ©Ã©s');
        return $users;
    }

    /**
     * Seed public pages (pages statiques de l'OPAC)
     */
    private function seedPublicPages()
    {
        $this->command->info('ðŸ“„ CrÃ©ation des pages statiques...');

        $admin = User::first();
        if (!$admin) {
            $this->command->warn('âš ï¸  Aucun utilisateur admin trouvÃ© pour les pages');
            return;
        }

        // Page d'accueil
        PublicPage::updateOrCreate(
            ['slug' => 'accueil'],
            [
                'title' => 'Bienvenue Ã  la BibliothÃ¨que',
                'name' => 'Accueil',
                'content' => $this->getHomePageContent(),
                'meta_description' => 'DÃ©couvrez nos collections, services et ressources en ligne',
                'meta_keywords' => 'bibliothÃ¨que, catalogue, livres, ressources, culture',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 1,
                'parent_id' => null,
                'is_published' => true,
                'featured_image_path' => '/images/library-homepage.jpg'
            ]
        );

        // Page Ã€ propos
        PublicPage::updateOrCreate(
            ['slug' => 'a-propos'],
            [
                'title' => 'Ã€ propos de la bibliothÃ¨que',
                'name' => 'Ã€ propos',
                'content' => $this->getAboutPageContent(),
                'meta_description' => 'Histoire, mission et Ã©quipe de notre bibliothÃ¨que',
                'meta_keywords' => 'histoire, mission, Ã©quipe, bibliothÃ¨que',
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
                'meta_description' => 'DÃ©couvrez tous les services offerts par notre bibliothÃ¨que',
                'meta_keywords' => 'services, prÃªt, consultation, formation, aide',
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
                'title' => 'PrÃªt de documents',
                'name' => 'PrÃªt de documents',
                'content' => $this->getLoanServiceContent(),
                'meta_description' => 'Conditions et modalitÃ©s de prÃªt des documents',
                'meta_keywords' => 'prÃªt, documents, livres, conditions',
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
                'meta_description' => 'Formations et ateliers proposÃ©s par la bibliothÃ¨que',
                'meta_keywords' => 'formations, ateliers, numÃ©rique, recherche',
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
                'meta_description' => 'CoordonnÃ©es et horaires de la bibliothÃ¨que',
                'meta_keywords' => 'contact, adresse, tÃ©lÃ©phone, horaires',
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
                'meta_description' => 'Questions frÃ©quentes et aide pour utiliser le catalogue',
                'meta_keywords' => 'aide, FAQ, questions, recherche, catalogue',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 5,
                'parent_id' => null,
                'is_published' => true
            ]
        );

        // Page Mentions lÃ©gales
        PublicPage::updateOrCreate(
            ['slug' => 'mentions-legales'],
            [
                'title' => 'Mentions lÃ©gales',
                'name' => 'Mentions lÃ©gales',
                'content' => $this->getLegalPageContent(),
                'meta_description' => 'Mentions lÃ©gales et politique de confidentialitÃ©',
                'meta_keywords' => 'mentions lÃ©gales, confidentialitÃ©, RGPD',
                'status' => 'published',
                'author_id' => $admin->id,
                'order' => 6,
                'parent_id' => null,
                'is_published' => true
            ]
        );

        $this->command->info('âœ… Pages statiques crÃ©Ã©es');
    }

    /**
     * Seed public news (actualitÃ©s)
     */
    private function seedPublicNews()
    {
        $this->command->info('ðŸ“° CrÃ©ation des actualitÃ©s...');

        $admin = User::first();
        if (!$admin) {
            $this->command->warn('âš ï¸  Aucun utilisateur admin trouvÃ© pour les actualitÃ©s');
            return;
        }

        $newsItems = [
            [
                'title' => 'Nouvelle exposition : "L\'Art Ã  travers les siÃ¨cles"',
                'slug' => 'nouvelle-exposition-art-siecles',
                'summary' => 'DÃ©couvrez notre nouvelle exposition permanente dÃ©diÃ©e Ã  l\'art occidental',
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
                'title' => 'Ateliers numÃ©riques : inscriptions ouvertes',
                'slug' => 'ateliers-numeriques-inscriptions-ouvertes',
                'summary' => 'Apprenez Ã  maÃ®triser les outils numÃ©riques avec nos ateliers gratuits',
                'content' => $this->getDigitalWorkshopsNewsContent(),
                'image_path' => '/images/news/ateliers-numeriques.jpg',
                'published_at' => now()->subDays(3),
                'status' => 'published',
                'featured' => true
            ],
            [
                'title' => 'Extension des horaires d\'ouverture',
                'slug' => 'extension-horaires-ouverture',
                'summary' => 'Ã€ partir du 1er dÃ©cembre, nouveaux horaires Ã©tendus',
                'content' => $this->getExtendedHoursNewsContent(),
                'image_path' => '/images/news/horaires.jpg',
                'published_at' => now()->subDays(15),
                'status' => 'published',
                'featured' => false
            ],
            [
                'title' => 'Concours de nouvelles 2024 : participez !',
                'slug' => 'concours-nouvelles-2024',
                'summary' => 'Le concours annuel de nouvelles est ouvert Ã  tous les rÃ©sidents',
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
                    'is_published' => true, // Publier les actualitÃ©s
                    'status' => 'published'
                ])
            );
        }

        $this->command->info('âœ… ' . count($newsItems) . ' actualitÃ©s crÃ©Ã©es');
    }

    /**
     * Seed public events (Ã©vÃ©nements et calendrier)
     */
    private function seedPublicEvents($publicUsers)
    {
        $this->command->info('ðŸ“… CrÃ©ation des Ã©vÃ©nements...');

        $events = [];

        // ConfÃ©rence Ã  venir
        $events[] = PublicEvent::updateOrCreate(
            ['name' => 'ConfÃ©rence : "L\'Intelligence Artificielle et l\'Ã‰ducation"'],
            [
                'description' => $this->getAIConferenceEventContent(),
                'start_date' => now()->addDays(10)->setHour(18)->setMinute(30),
                'end_date' => now()->addDays(10)->setHour(20)->setMinute(0),
                'location' => 'Auditorium de la bibliothÃ¨que - 1er Ã©tage',
                'is_online' => false,
                'online_link' => null
            ]
        );

        // Atelier numÃ©rique en ligne
        $events[] = PublicEvent::updateOrCreate(
            ['name' => 'Atelier : Recherche documentaire avancÃ©e'],
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
            ['name' => 'Club de lecture - "La littÃ©rature contemporaine"'],
            [
                'description' => $this->getBookClubEventContent(),
                'start_date' => now()->addDays(15)->setHour(19)->setMinute(0),
                'end_date' => now()->addDays(15)->setHour(21)->setMinute(0),
                'location' => 'Salon de lecture - Rez-de-chaussÃ©e',
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
                'location' => 'Galerie d\'exposition - 2Ã¨me Ã©tage',
                'is_online' => false,
                'online_link' => null
            ]
        );

        // Formation aux outils numÃ©riques
        $events[] = PublicEvent::updateOrCreate(
            ['name' => 'Formation : "MaÃ®triser les tablettes et smartphones"'],
            [
                'description' => $this->getDigitalTrainingEventContent(),
                'start_date' => now()->addDays(12)->setHour(10)->setMinute(0),
                'end_date' => now()->addDays(12)->setHour(12)->setMinute(0),
                'location' => 'Salle informatique - 1er Ã©tage',
                'is_online' => false,
                'online_link' => null
            ]
        );

        // Ã‰vÃ©nement passÃ© pour l'historique
        $events[] = PublicEvent::updateOrCreate(
            ['name' => 'JournÃ©e Portes Ouvertes - Ã‰dition 2024'],
            [
                'description' => $this->getOpenDayEventContent(),
                'start_date' => now()->subDays(30)->setHour(9)->setMinute(0),
                'end_date' => now()->subDays(30)->setHour(17)->setMinute(0),
                'location' => 'Ensemble de la bibliothÃ¨que',
                'is_online' => false,
                'online_link' => null
            ]
        );

        // CrÃ©er quelques inscriptions d'exemple
        $this->seedEventRegistrations($events, $publicUsers);

        $this->command->info('âœ… ' . count($events) . ' Ã©vÃ©nements crÃ©Ã©s');
    }

    /**
     * Seed event registrations
     */
    private function seedEventRegistrations($events, $publicUsers)
    {
        if (empty($publicUsers) || empty($events)) {
            return;
        }

        $this->command->info('ðŸ“ CrÃ©ation des inscriptions aux Ã©vÃ©nements...');

        $registrationCount = 0;

        // CrÃ©ons quelques inscriptions d'exemple pour les Ã©vÃ©nements qui ont des participants
        foreach ($events as $event) {
            // Simuler des inscriptions pour certains Ã©vÃ©nements
            if (in_array($event->name, [
                'ConfÃ©rence : "L\'Intelligence Artificielle et l\'Ã‰ducation"',
                'Atelier : Recherche documentaire avancÃ©e',
                'Club de lecture - "La littÃ©rature contemporaine"'
            ])) {
                                // Inscrire 2-3 utilisateurs par Ã©vÃ©nement
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
                        // Ignore si la table n'existe pas ou a une structure diffÃ©rente
                        $this->command->warn("Impossible de crÃ©er les inscriptions: " . $e->getMessage());
                        break 2; // Sortir des deux boucles
                    }
                }
            }
        }        $this->command->info("âœ… {$registrationCount} inscriptions crÃ©Ã©es");
    }

    /**
     * Seed public records (documents publics)
     */
    private function seedPublicRecords()
    {
        $this->command->info('ðŸ“š CrÃ©ation des enregistrements publics...');

        // VÃ©rifier s'il y a des records existants
        $existingRecords = Record::limit(10)->get();

        if ($existingRecords->isEmpty()) {
            $this->command->warn('âš ï¸  Aucun enregistrement trouvÃ©, crÃ©ation ignorÃ©e');
            return;
        }

        $admin = User::first();
        $recordsPublished = 0;

        foreach ($existingRecords as $record) {
            // Publier alÃ©atoirement certains enregistrements
            if (rand(1, 3) === 1) { // 1 chance sur 3
                PublicRecord::updateOrCreate(
                    ['record_id' => $record->id],
                    [
                        'published_at' => now()->subDays(rand(1, 90)),
                        'expires_at' => null, // Pas d'expiration par dÃ©faut
                        'published_by' => $admin->id,
                        'publication_notes' => 'Document publiÃ© automatiquement via seeder OPAC'
                    ]
                );
                $recordsPublished++;
            }
        }

        $this->command->info("âœ… {$recordsPublished} enregistrements publiÃ©s sur l'OPAC");
    }

    /**
     * Seed public templates
     */
    private function seedPublicTemplates()
    {
        $this->command->info('ðŸŽ¨ CrÃ©ation des templates publics...');

        $templates = [
            [
                'name' => 'Template Recherche AvancÃ©e',
                'description' => 'Template personnalisÃ© pour la recherche avancÃ©e',
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
                    'title' => 'Recherche AvancÃ©e',
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
                    'welcome_message' => 'Explorez nos collections et dÃ©couvrez de nouvelles ressources',
                    'featured_title' => 'Collections mises en avant',
                    'recent_title' => 'Nouvelles acquisitions'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Template Liste RÃ©sultats',
                'description' => 'Template d\'affichage des rÃ©sultats de recherche',
                'type' => 'page',
                'content' => '<div class="results"><h2>RÃ©sultats</h2><div class="count">{{results_count_format}}</div><div class="items"><!-- results --></div></div>',
                'status' => 'active',
                'parameters' => [
                    'view_mode' => 'list', // list ou grid
                    'show_thumbnails' => true,
                    'show_summary' => true,
                    'show_availability' => true,
                    'items_per_page' => 20
                ],
                'values' => [
                    'no_results_message' => 'Aucun rÃ©sultat trouvÃ© pour votre recherche',
                    'results_count_format' => '{count} rÃ©sultat(s) trouvÃ©(s)',
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

        $this->command->info('âœ… ' . count($templates) . ' templates publics crÃ©Ã©s');
    }

    // MÃ©thodes pour le contenu des pages
    private function getHomePageContent()
    {
        return '<div class="welcome-section">
            <h1>Bienvenue Ã  la BibliothÃ¨que Municipale</h1>
            <p class="lead">DÃ©couvrez nos collections riches et variÃ©es, nos services innovants et nos espaces de travail modernes. Notre bibliothÃ¨que est un lieu de savoir, de culture et d\'Ã©change pour tous.</p>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <h3>ðŸ” Rechercher</h3>
                        <p>Explorez notre catalogue de plus de 50 000 documents : livres, revues, documents numÃ©riques, DVD...</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <h3>ðŸ“š Emprunter</h3>
                        <p>Empruntez jusqu\'Ã  10 documents pour 3 semaines. Prolongez vos prÃªts facilement en ligne.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <h3>ðŸŽ“ Apprendre</h3>
                        <p>Participez Ã  nos ateliers, formations et Ã©vÃ©nements culturels tout au long de l\'annÃ©e.</p>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getAboutPageContent()
    {
        return '<h1>Ã€ propos de notre bibliothÃ¨que</h1>

        <h2>Notre histoire</h2>
        <p>CrÃ©Ã©e en 1950, la BibliothÃ¨que Municipale est devenue au fil des dÃ©cennies un pilier culturel de notre ville. RÃ©novÃ©e en 2015, elle allie patrimoine architectural et modernitÃ© technologique.</p>

        <h2>Notre mission</h2>
        <p>Nous nous engageons Ã  :</p>
        <ul>
            <li>Favoriser l\'accÃ¨s Ã  l\'information et Ã  la culture pour tous</li>
            <li>Accompagner les usagers dans leurs recherches et projets</li>
            <li>Promouvoir la lecture et les pratiques culturelles</li>
            <li>Soutenir l\'Ã©ducation et la formation tout au long de la vie</li>
        </ul>

        <h2>Notre Ã©quipe</h2>
        <p>Une Ã©quipe de 12 professionnels passionnÃ©s vous accueille et vous conseille : bibliothÃ©caires, mÃ©diathÃ©caires, animateurs culturels et personnel d\'accueil.</p>';
    }

    private function getServicesPageContent()
    {
        return '<h1>Nos services</h1>

        <div class="services-grid">
            <div class="service-item">
                <h3>ðŸ“– PrÃªt et consultation</h3>
                <p>Empruntez ou consultez sur place nos documents. AccÃ¨s libre Ã  tous nos espaces de lecture.</p>
            </div>

            <div class="service-item">
                <h3>ðŸ’» AccÃ¨s numÃ©rique</h3>
                <p>Wifi gratuit, postes informatiques en libre accÃ¨s, ressources numÃ©riques en ligne.</p>
            </div>

            <div class="service-item">
                <h3>ðŸ‘¥ Aide personnalisÃ©e</h3>
                <p>Accompagnement dans vos recherches documentaires et formations aux outils numÃ©riques.</p>
            </div>

            <div class="service-item">
                <h3>ðŸŽ­ Animation culturelle</h3>
                <p>ConfÃ©rences, expositions, clubs de lecture, ateliers crÃ©atifs pour tous les Ã¢ges.</p>
            </div>
        </div>';
    }

    private function getLoanServiceContent()
    {
        return '<h1>PrÃªt de documents</h1>

        <h2>Conditions de prÃªt</h2>
        <ul>
            <li><strong>Nombre de documents :</strong> 10 maximum par carte</li>
            <li><strong>DurÃ©e :</strong> 3 semaines (21 jours)</li>
            <li><strong>Prolongation :</strong> 2 prolongations possibles si le document n\'est pas rÃ©servÃ©</li>
            <li><strong>RÃ©servation :</strong> 5 rÃ©servations maximum par carte</li>
        </ul>

        <h2>Tarifs</h2>
        <ul>
            <li>RÃ©sidents de la ville : Gratuit</li>
            <li>ExtÃ©rieurs : 25â‚¬ par an</li>
            <li>Ã‰tudiants/demandeurs d\'emploi : 10â‚¬ par an</li>
        </ul>';
    }

    private function getTrainingPageContent()
    {
        return '<h1>Formations et ateliers</h1>

        <h2>Formations numÃ©riques</h2>
        <ul>
            <li>Initiation Ã  l\'informatique</li>
            <li>Internet et navigation web</li>
            <li>Messagerie Ã©lectronique</li>
            <li>DÃ©marches administratives en ligne</li>
        </ul>

        <h2>Ateliers crÃ©atifs</h2>
        <ul>
            <li>Ã‰criture crÃ©ative</li>
            <li>GÃ©nÃ©alogie</li>
            <li>Retouche photo</li>
            <li>CrÃ©ation de blogs</li>
        </ul>

        <p><strong>Inscription obligatoire</strong> - Places limitÃ©es</p>';
    }

    private function getContactPageContent()
    {
        return '<h1>Nous contacter</h1>

        <div class="contact-info">
            <div class="contact-section">
                <h2>ðŸ“ Adresse</h2>
                <p>123 Rue de la Culture<br>
                12345 Ville Test<br>
                France</p>
            </div>

            <div class="contact-section">
                <h2>ðŸ“ž TÃ©lÃ©phone</h2>
                <p>+33 1 23 45 67 89</p>
            </div>

            <div class="contact-section">
                <h2>âœ‰ï¸ Email</h2>
                <p>contact@bibliotheque-test.fr</p>
            </div>

            <div class="contact-section">
                <h2>ðŸ• Horaires</h2>
                <ul>
                    <li>Lundi - Vendredi : 9h00 - 18h00</li>
                    <li>Jeudi : 9h00 - 20h00 (nocturne)</li>
                    <li>Samedi : 9h00 - 17h00</li>
                    <li>Dimanche : FermÃ©</li>
                </ul>
            </div>
        </div>';
    }

    private function getHelpPageContent()
    {
        return '<h1>Aide et FAQ</h1>

        <div class="faq-section">
            <h2>â“ Questions frÃ©quentes</h2>

            <div class="faq-item">
                <h3>Comment rechercher un document ?</h3>
                <p>Utilisez le moteur de recherche en saisissant le titre, l\'auteur ou des mots-clÃ©s. Vous pouvez Ã©galement utiliser la recherche avancÃ©e pour affiner vos critÃ¨res.</p>
            </div>

            <div class="faq-item">
                <h3>Comment prolonger mes prÃªts ?</h3>
                <p>Connectez-vous Ã  votre compte et accÃ©dez Ã  la section "Mes prÃªts". Cliquez sur "Prolonger" Ã  cÃ´tÃ© du document souhaitÃ©.</p>
            </div>

            <div class="faq-item">
                <h3>Comment rÃ©server un document ?</h3>
                <p>Sur la page du document, cliquez sur "RÃ©server". Vous serez averti par email dÃ¨s que le document sera disponible.</p>
            </div>
        </div>';
    }

    private function getLegalPageContent()
    {
        return '<h1>Mentions lÃ©gales</h1>

        <h2>Ã‰diteur du site</h2>
        <p>BibliothÃ¨que Municipale de Test<br>
        123 Rue de la Culture, 12345 Ville Test<br>
        TÃ©lÃ©phone : +33 1 23 45 67 89<br>
        Email : contact@bibliotheque-test.fr</p>

        <h2>HÃ©bergement</h2>
        <p>Ce site est hÃ©bergÃ© par notre fournisseur de services cloud.</p>

        <h2>Protection des donnÃ©es personnelles</h2>
        <p>ConformÃ©ment au RGPD, nous nous engageons Ã  protÃ©ger vos donnÃ©es personnelles. Pour toute question, contactez notre DPO Ã  l\'adresse : dpo@bibliotheque-test.fr</p>';
    }

    // MÃ©thodes pour le contenu des actualitÃ©s
    private function getArtExhibitionNewsContent()
    {
        return '<p>Nous sommes ravis de vous prÃ©senter notre nouvelle exposition permanente <strong>"L\'Art Ã  travers les siÃ¨cles"</strong>.</p>

        <p>Cette exposition retrace l\'Ã©volution de l\'art occidental du Moyen Ã‚ge Ã  nos jours Ã  travers une sÃ©lection d\'Å“uvres reproduites et de documents d\'archives exceptionnels.</p>

        <h3>Au programme :</h3>
        <ul>
            <li>Art mÃ©diÃ©val et renaissance</li>
            <li>Les grands maÃ®tres classiques</li>
            <li>L\'art moderne et contemporain</li>
            <li>Ateliers dÃ©couverte pour enfants</li>
        </ul>

        <p><strong>EntrÃ©e libre</strong> - Du lundi au samedi aux heures d\'ouverture de la bibliothÃ¨que.</p>';
    }

    private function getScienceBooksNewsContent()
    {
        return '<p>Bonne nouvelle pour les passionnÃ©s de sciences ! Notre collection s\'enrichit de <strong>500 nouveaux ouvrages</strong> dans tous les domaines scientifiques.</p>

        <h3>NouveautÃ©s par discipline :</h3>
        <ul>
            <li>Physique et astronomie : 120 ouvrages</li>
            <li>Biologie et mÃ©decine : 150 ouvrages</li>
            <li>MathÃ©matiques et informatique : 100 ouvrages</li>
            <li>Sciences de la terre : 80 ouvrages</li>
            <li>Chimie : 50 ouvrages</li>
        </ul>

        <p>Ces acquisitions incluent les derniÃ¨res parutions de 2024, des manuels universitaires et des ouvrages de vulgarisation scientifique.</p>';
    }

    private function getDigitalWorkshopsNewsContent()
    {
        return '<p>DÃ©veloppez vos compÃ©tences numÃ©riques avec nos <strong>ateliers gratuits</strong> !</p>

        <h3>Prochaines sessions :</h3>
        <ul>
            <li><strong>15 dÃ©cembre :</strong> Initiation aux tablettes (14h-16h)</li>
            <li><strong>18 dÃ©cembre :</strong> DÃ©marches administratives en ligne (10h-12h)</li>
            <li><strong>22 dÃ©cembre :</strong> CrÃ©er et gÃ©rer ses mots de passe (14h-15h30)</li>
        </ul>

        <p><strong>Inscription obligatoire</strong> au bureau d\'accueil ou par tÃ©lÃ©phone. Places limitÃ©es Ã  8 participants par atelier.</p>';
    }

    private function getExtendedHoursNewsContent()
    {
        return '<p>Pour mieux vous servir, nous Ã©tendons nos horaires d\'ouverture Ã  partir du <strong>1er dÃ©cembre 2024</strong>.</p>

        <h3>Nouveaux horaires :</h3>
        <ul>
            <li><strong>Lundi Ã  mercredi :</strong> 9h00 - 19h00 (au lieu de 18h00)</li>
            <li><strong>Jeudi :</strong> 9h00 - 21h00 (nocturne Ã©tendue)</li>
            <li><strong>Vendredi :</strong> 9h00 - 19h00</li>
            <li><strong>Samedi :</strong> 9h00 - 18h00 (au lieu de 17h00)</li>
        </ul>

        <p>Cette extension rÃ©pond Ã  vos demandes pour plus de flexibilitÃ© dans vos visites.</p>';
    }

    private function getWritingContestNewsContent()
    {
        return '<p>Le <strong>concours de nouvelles 2024</strong> est lancÃ© ! ThÃ¨me cette annÃ©e : "Voyages extraordinaires".</p>

        <h3>ModalitÃ©s :</h3>
        <ul>
            <li><strong>Public :</strong> Tous les rÃ©sidents de plus de 16 ans</li>
            <li><strong>Format :</strong> Nouvelle de 5 Ã  15 pages</li>
            <li><strong>Date limite :</strong> 31 janvier 2025</li>
            <li><strong>Remise des prix :</strong> 15 mars 2025</li>
        </ul>

        <h3>Prix :</h3>
        <ul>
            <li>1er prix : 500â‚¬ + publication</li>
            <li>2e prix : 300â‚¬</li>
            <li>3e prix : 200â‚¬</li>
            <li>Prix coup de cÅ“ur du public : 100â‚¬</li>
        </ul>

        <p>RÃ¨glement complet disponible Ã  l\'accueil et sur notre site.</p>';
    }

    // MÃ©thodes pour le contenu des Ã©vÃ©nements
    private function getAIConferenceEventContent()
    {
        return 'ConfÃ©rence exceptionnelle animÃ©e par le Dr. Sarah Martinez, spÃ©cialiste en IA Ã©ducative.

        Au programme :
        - Impact de l\'IA sur les mÃ©thodes d\'apprentissage
        - Outils d\'IA pour l\'Ã©ducation
        - Enjeux Ã©thiques et perspectives d\'avenir
        - Session de questions-rÃ©ponses

        EntrÃ©e libre sur inscription. Cocktail offert Ã  l\'issue de la confÃ©rence.';
    }

    private function getAdvancedSearchWorkshopContent()
    {
        return 'Atelier pratique en ligne pour maÃ®triser les techniques de recherche documentaire avancÃ©e.

        Vous apprendrez Ã  :
        - Utiliser les opÃ©rateurs boolÃ©ens
        - Exploiter les bases de donnÃ©es spÃ©cialisÃ©es
        - Ã‰valuer la fiabilitÃ© des sources
        - Organiser votre veille informationnelle

        MatÃ©riel requis : ordinateur avec connexion internet stable.';
    }

    private function getBookClubEventContent()
    {
        return 'Rencontre mensuelle de notre club de lecture dÃ©diÃ©e Ã  la littÃ©rature contemporaine franÃ§aise.

        Ce mois-ci, nous discuterons de :
        - "Yoga" d\'Emmanuel CarrÃ¨re
        - "Civilizations" de Laurent Binet
        - "L\'Anomalie" d\'HervÃ© Le Tellier

        Que vous ayez lu un ou tous ces livres, votre participation enrichira nos Ã©changes !';
    }

    private function getPhotographyExhibitionContent()
    {
        return 'Exposition photographique prÃ©sentant le riche patrimoine architectural et naturel de notre rÃ©gion.

        DÃ©couvrez :
        - 40 photographies d\'exception
        - ChÃ¢teaux et monuments historiques
        - Paysages prÃ©servÃ©s
        - Rencontres avec les photographes les week-ends

        Exposition accessible pendant les horaires d\'ouverture. Visite guidÃ©e possible sur demande.';
    }

    private function getDigitalTrainingEventContent()
    {
        return 'Formation pratique aux tablettes et smartphones pour les dÃ©butants et les utilisateurs souhaitant se perfectionner.

        Programme :
        - Prise en main et navigation
        - Installation et gestion des applications
        - SÃ©curitÃ© et paramÃ¨tres de confidentialitÃ©
        - Astuces et bonnes pratiques

        MatÃ©riel fourni. Apportez votre propre appareil si vous en avez un.';
    }

    private function getOpenDayEventContent()
    {
        return 'JournÃ©e portes ouvertes exceptionnelle avec de nombreuses animations pour dÃ©couvrir tous nos services.

        Programme de la journÃ©e :
        - Visites guidÃ©es toutes les heures
        - DÃ©monstrations des ressources numÃ©riques
        - Ateliers dÃ©couverte pour enfants
        - Rencontre avec l\'Ã©quipe
        - Exposition des nouveautÃ©s
        - Buffet de l\'amitiÃ©

        EntrÃ©e libre, venez nombreux !';
    }
}

