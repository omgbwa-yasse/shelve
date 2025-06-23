<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PublicPortalSeeder1 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Public Users
        DB::table('public_users')->insert([
            [
                'id' => 1,
                'name' => 'Dupont',
                'first_name' => 'Jean',
                'phone1' => '514-555-0101',
                'phone2' => '514-555-0102',
                'address' => '123 Rue Saint-Denis, Montréal, QC H2X 3K8',
                'email' => 'jean.dupont@email.com',
                'email_verified_at' => '2025-01-15 10:00:00',
                'password' => Hash::make('password'),
                'is_approved' => true,
                'created_at' => '2025-01-10 09:00:00',
                'updated_at' => '2025-01-15 10:00:00'
            ],
            [
                'id' => 2,
                'name' => 'Martin',
                'first_name' => 'Sophie',
                'phone1' => '514-555-0201',
                'phone2' => '514-555-0202',
                'address' => '456 Boulevard René-Lévesque, Montréal, QC H3B 1L5',
                'email' => 'sophie.martin@email.com',
                'email_verified_at' => '2025-01-20 14:30:00',
                'password' => Hash::make('password'),
                'is_approved' => true,
                'created_at' => '2025-01-18 11:30:00',
                'updated_at' => '2025-01-20 14:30:00'
            ],
            [
                'id' => 3,
                'name' => 'Tremblay',
                'first_name' => 'Pierre',
                'phone1' => '514-555-0301',
                'phone2' => null,
                'address' => '789 Rue Sherbrooke, Montréal, QC H3A 1E3',
                'email' => 'pierre.tremblay@email.com',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
                'is_approved' => false,
                'created_at' => '2025-02-01 16:45:00',
                'updated_at' => '2025-02-01 16:45:00'
            ],
            [
                'id' => 4,
                'name' => 'Lavoie',
                'first_name' => 'Marie',
                'phone1' => '514-555-0401',
                'phone2' => '514-555-0402',
                'address' => '321 Avenue du Parc, Montréal, QC H2W 2N4',
                'email' => 'marie.lavoie@email.com',
                'email_verified_at' => '2025-02-10 08:15:00',
                'password' => Hash::make('password'),
                'is_approved' => true,
                'created_at' => '2025-02-05 13:20:00',
                'updated_at' => '2025-02-10 08:15:00'
            ],
            [
                'id' => 5,
                'name' => 'Bouchard',
                'first_name' => 'Luc',
                'phone1' => '514-555-0501',
                'phone2' => '514-555-0502',
                'address' => '654 Rue Ontario, Montréal, QC H2L 1N8',
                'email' => 'luc.bouchard@email.com',
                'email_verified_at' => '2025-02-15 12:00:00',
                'password' => Hash::make('password'),
                'is_approved' => true,
                'created_at' => '2025-02-12 10:10:00',
                'updated_at' => '2025-02-15 12:00:00'
            ]
        ]);

        // 2. Public Templates
        DB::table('public_templates')->insert([
            [
                'id' => 1,
                'name' => 'Demande de permis',
                'description' => 'Modèle pour les demandes de permis de construction',
                'parameters' => json_encode(['nom_projet', 'adresse', 'type_construction', 'superficie']),
                'values' => json_encode([
                    'nom_projet' => '',
                    'adresse' => '',
                    'type_construction' => 'résidentiel',
                    'superficie' => ''
                ]),
                'is_active' => true,
                'created_at' => '2025-01-05 09:00:00',
                'updated_at' => '2025-01-05 09:00:00'
            ],
            [
                'id' => 2,
                'name' => 'Certificat de naissance',
                'description' => 'Modèle pour les demandes de certificat de naissance',
                'parameters' => json_encode(['nom_complet', 'date_naissance', 'lieu_naissance', 'nom_parents']),
                'values' => json_encode([
                    'nom_complet' => '',
                    'date_naissance' => '',
                    'lieu_naissance' => 'Montréal',
                    'nom_parents' => ''
                ]),
                'is_active' => true,
                'created_at' => '2025-01-05 09:30:00',
                'updated_at' => '2025-01-05 09:30:00'
            ],
            [
                'id' => 3,
                'name' => 'Plainte citoyenne',
                'description' => 'Modèle pour soumettre une plainte',
                'parameters' => json_encode(['type_plainte', 'lieu_incident', 'description', 'urgence']),
                'values' => json_encode([
                    'type_plainte' => '',
                    'lieu_incident' => '',
                    'description' => '',
                    'urgence' => 'normale'
                ]),
                'is_active' => true,
                'created_at' => '2025-01-05 10:00:00',
                'updated_at' => '2025-01-05 10:00:00'
            ],
            [
                'id' => 4,
                'name' => 'Demande d\'information',
                'description' => 'Modèle générique pour demandes d\'information',
                'parameters' => json_encode(['sujet', 'departement', 'description']),
                'values' => json_encode([
                    'sujet' => '',
                    'departement' => '',
                    'description' => ''
                ]),
                'is_active' => false,
                'created_at' => '2025-01-05 10:30:00',
                'updated_at' => '2025-02-01 14:00:00'
            ]
        ]);

        // 3. Public Records (Nécessite l'existence des tables 'records' et 'users')
        DB::table('public_records')->insert([
            [
                'id' => 1,
                'record_id' => 1, // Ajustez selon vos données existantes
                'published_at' => '2025-01-10 10:00:00',
                'expires_at' => '2025-12-31 23:59:59',
                'published_by' => 1, // Ajustez selon vos données existantes
                'publication_notes' => 'Publication du budget municipal 2025',
                'created_at' => '2025-01-10 09:45:00',
                'updated_at' => '2025-01-10 10:00:00'
            ],
            [
                'id' => 2,
                'record_id' => 2,
                'published_at' => '2025-01-15 14:00:00',
                'expires_at' => null,
                'published_by' => 1,
                'publication_notes' => 'Procès-verbal du conseil municipal du 14 janvier',
                'created_at' => '2025-01-15 13:30:00',
                'updated_at' => '2025-01-15 14:00:00'
            ],
            [
                'id' => 3,
                'record_id' => 3,
                'published_at' => '2025-01-20 08:30:00',
                'expires_at' => '2025-06-30 23:59:59',
                'published_by' => 2,
                'publication_notes' => 'Appel d\'offres - Rénovation du parc central',
                'created_at' => '2025-01-20 08:00:00',
                'updated_at' => '2025-01-20 08:30:00'
            ],
            [
                'id' => 4,
                'record_id' => 4,
                'published_at' => '2025-02-01 11:00:00',
                'expires_at' => '2025-03-31 23:59:59',
                'published_by' => 2,
                'publication_notes' => 'Règlement sur le stationnement - Modification',
                'created_at' => '2025-02-01 10:30:00',
                'updated_at' => '2025-02-01 11:00:00'
            ],
            [
                'id' => 5,
                'record_id' => 5,
                'published_at' => '2025-02-05 16:00:00',
                'expires_at' => null,
                'published_by' => 1,
                'publication_notes' => 'Plan d\'urbanisme - Zone résidentielle nord',
                'created_at' => '2025-02-05 15:45:00',
                'updated_at' => '2025-02-05 16:00:00'
            ],
            [
                'id' => 6,
                'record_id' => 6,
                'published_at' => '2025-02-10 09:00:00',
                'expires_at' => '2025-08-31 23:59:59',
                'published_by' => 2,
                'publication_notes' => 'Permis de construction approuvés - Janvier 2025',
                'created_at' => '2025-02-10 08:45:00',
                'updated_at' => '2025-02-10 09:00:00'
            ]
        ]);

        // 4. Public Events
        DB::table('public_events')->insert([
            [
                'id' => 1,
                'name' => 'Assemblée publique - Budget 2025',
                'description' => 'Présentation et discussion du budget municipal pour l\'année 2025',
                'start_date' => '2025-03-15 19:00:00',
                'end_date' => '2025-03-15 21:00:00',
                'location' => 'Hôtel de ville, Salle du conseil',
                'is_online' => false,
                'online_link' => null,
                'created_at' => '2025-02-01 10:00:00',
                'updated_at' => '2025-02-01 10:00:00'
            ],
            [
                'id' => 2,
                'name' => 'Consultation citoyenne - Parc central',
                'description' => 'Consultation publique sur la rénovation du parc central',
                'start_date' => '2025-03-20 18:30:00',
                'end_date' => '2025-03-20 20:30:00',
                'location' => 'Centre communautaire Saint-Denis',
                'is_online' => false,
                'online_link' => null,
                'created_at' => '2025-02-05 14:00:00',
                'updated_at' => '2025-02-05 14:00:00'
            ],
            [
                'id' => 3,
                'name' => 'Webinaire - Services numériques',
                'description' => 'Présentation des nouveaux services numériques de la ville',
                'start_date' => '2025-03-25 12:00:00',
                'end_date' => '2025-03-25 13:00:00',
                'location' => null,
                'is_online' => true,
                'online_link' => 'https://zoom.us/j/123456789',
                'created_at' => '2025-02-10 09:30:00',
                'updated_at' => '2025-02-10 09:30:00'
            ],
            [
                'id' => 4,
                'name' => 'Forum jeunesse',
                'description' => 'Rencontre avec les jeunes de 16-25 ans sur leurs besoins',
                'start_date' => '2025-04-05 14:00:00',
                'end_date' => '2025-04-05 17:00:00',
                'location' => 'Maison des jeunes',
                'is_online' => false,
                'online_link' => null,
                'created_at' => '2025-02-15 11:00:00',
                'updated_at' => '2025-02-15 11:00:00'
            ],
            [
                'id' => 5,
                'name' => 'Journée portes ouvertes',
                'description' => 'Visite guidée des installations municipales',
                'start_date' => '2025-04-12 10:00:00',
                'end_date' => '2025-04-12 16:00:00',
                'location' => 'Divers sites municipaux',
                'is_online' => false,
                'online_link' => null,
                'created_at' => '2025-02-20 16:00:00',
                'updated_at' => '2025-02-20 16:00:00'
            ]
        ]);

        // 5. Public Event Registrations
        DB::table('public_event_registrations')->insert([
            [
                'id' => 1,
                'event_id' => 1,
                'user_id' => 1,
                'status' => 'confirmed',
                'registered_at' => '2025-02-05 10:30:00',
                'notes' => 'Intéressé par les questions de transport',
                'created_at' => '2025-02-05 10:30:00',
                'updated_at' => '2025-02-08 14:00:00'
            ],
            [
                'id' => 2,
                'event_id' => 1,
                'user_id' => 2,
                'status' => 'registered',
                'registered_at' => '2025-02-08 16:45:00',
                'notes' => null,
                'created_at' => '2025-02-08 16:45:00',
                'updated_at' => '2025-02-08 16:45:00'
            ],
            [
                'id' => 3,
                'event_id' => 2,
                'user_id' => 1,
                'status' => 'registered',
                'registered_at' => '2025-02-10 09:15:00',
                'notes' => 'Résident près du parc',
                'created_at' => '2025-02-10 09:15:00',
                'updated_at' => '2025-02-10 09:15:00'
            ],
            [
                'id' => 4,
                'event_id' => 2,
                'user_id' => 4,
                'status' => 'confirmed',
                'registered_at' => '2025-02-12 14:20:00',
                'notes' => 'Représentante du comité de quartier',
                'created_at' => '2025-02-12 14:20:00',
                'updated_at' => '2025-02-15 11:30:00'
            ],
            [
                'id' => 5,
                'event_id' => 3,
                'user_id' => 2,
                'status' => 'registered',
                'registered_at' => '2025-02-15 11:00:00',
                'notes' => null,
                'created_at' => '2025-02-15 11:00:00',
                'updated_at' => '2025-02-15 11:00:00'
            ],
            [
                'id' => 6,
                'event_id' => 3,
                'user_id' => 5,
                'status' => 'registered',
                'registered_at' => '2025-02-16 13:30:00',
                'notes' => 'Travailleur autonome intéressé',
                'created_at' => '2025-02-16 13:30:00',
                'updated_at' => '2025-02-16 13:30:00'
            ],
            [
                'id' => 7,
                'event_id' => 4,
                'user_id' => 4,
                'status' => 'cancelled',
                'registered_at' => '2025-02-18 10:00:00',
                'notes' => 'Conflit d\'horaire',
                'created_at' => '2025-02-18 10:00:00',
                'updated_at' => '2025-02-20 09:15:00'
            ]
        ]);

        // 6. Public Pages
        DB::table('public_pages')->insert([
            [
                'id' => 1,
                'name' => 'Accueil',
                'slug' => 'accueil',
                'content' => '<h1>Bienvenue sur le portail citoyen</h1><p>Accédez facilement aux services municipaux en ligne.</p>',
                'order' => 1,
                'parent_id' => null,
                'is_published' => true,
                'created_at' => '2025-01-01 08:00:00',
                'updated_at' => '2025-01-15 10:30:00'
            ],
            [
                'id' => 2,
                'name' => 'Services municipaux',
                'slug' => 'services',
                'content' => '<h1>Nos services</h1><p>Découvrez l\'ensemble des services offerts par votre municipalité.</p>',
                'order' => 2,
                'parent_id' => null,
                'is_published' => true,
                'created_at' => '2025-01-01 08:30:00',
                'updated_at' => '2025-01-10 14:20:00'
            ],
            [
                'id' => 3,
                'name' => 'Permis et licences',
                'slug' => 'permis-licences',
                'content' => '<h2>Permis et licences</h2><p>Demandez vos permis de construction, licences commerciales, etc.</p>',
                'order' => 1,
                'parent_id' => 2,
                'is_published' => true,
                'created_at' => '2025-01-01 09:00:00',
                'updated_at' => '2025-01-08 16:45:00'
            ],
            [
                'id' => 4,
                'name' => 'À propos',
                'slug' => 'a-propos',
                'content' => '<h1>À propos de notre ville</h1><p>Histoire, vision et mission de la municipalité.</p>',
                'order' => 3,
                'parent_id' => null,
                'is_published' => true,
                'created_at' => '2025-01-01 09:30:00',
                'updated_at' => '2025-01-05 11:15:00'
            ],
            [
                'id' => 5,
                'name' => 'Contact',
                'slug' => 'contact',
                'content' => '<h1>Nous contacter</h1><p>Coordonnées et heures d\'ouverture des bureaux municipaux.</p>',
                'order' => 4,
                'parent_id' => null,
                'is_published' => false,
                'created_at' => '2025-01-01 10:00:00',
                'updated_at' => '2025-02-01 13:00:00'
            ]
        ]);

        // 7. Public News
        DB::table('public_news')->insert([
            [
                'id' => 1,
                'name' => 'Nouveau portail citoyen en ligne',
                'slug' => 'nouveau-portail-citoyen',
                'content' => '<p>Nous sommes fiers de vous présenter notre nouveau portail citoyen qui simplifie vos démarches administratives.</p>',
                'user_id' => 1,
                'is_published' => true,
                'published_at' => '2025-01-15 10:00:00',
                'created_at' => '2025-01-14 15:30:00',
                'updated_at' => '2025-01-15 10:00:00'
            ],
            [
                'id' => 2,
                'name' => 'Travaux de réfection - Rue Principale',
                'slug' => 'travaux-rue-principale',
                'content' => '<p>Des travaux de réfection débuteront le 1er mars sur la rue Principale. Circulation modifiée pendant 3 semaines.</p>',
                'user_id' => 2,
                'is_published' => true,
                'published_at' => '2025-02-01 08:00:00',
                'created_at' => '2025-01-30 14:45:00',
                'updated_at' => '2025-02-01 08:00:00'
            ],
            [
                'id' => 3,
                'name' => 'Budget 2025 adopté',
                'slug' => 'budget-2025-adopte',
                'content' => '<p>Le conseil municipal a adopté le budget 2025 avec une augmentation de 2.5% des services aux citoyens.</p>',
                'user_id' => 1,
                'is_published' => true,
                'published_at' => '2025-02-10 16:30:00',
                'created_at' => '2025-02-10 14:20:00',
                'updated_at' => '2025-02-10 16:30:00'
            ],
            [
                'id' => 4,
                'name' => 'Programme de subventions écologiques',
                'slug' => 'programme-subventions-ecologiques',
                'content' => '<p>Nouveau programme de subventions pour l\'installation de bornes électriques résidentielles.</p>',
                'user_id' => 2,
                'is_published' => false,
                'published_at' => null,
                'created_at' => '2025-02-15 11:00:00',
                'updated_at' => '2025-02-18 09:30:00'
            ]
        ]);

        // 8. Public Search Logs
        DB::table('public_search_logs')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'search_term' => 'permis construction',
                'filters' => json_encode(['type' => 'document', 'date_range' => '2025']),
                'results_count' => 12,
                'created_at' => '2025-01-20 10:15:00',
                'updated_at' => '2025-01-20 10:15:00'
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'search_term' => 'budget 2025',
                'filters' => json_encode(['category' => 'finance']),
                'results_count' => 5,
                'created_at' => '2025-01-22 14:30:00',
                'updated_at' => '2025-01-22 14:30:00'
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'search_term' => 'assemblée publique',
                'filters' => json_encode(['date_range' => 'mars 2025']),
                'results_count' => 3,
                'created_at' => '2025-02-05 09:45:00',
                'updated_at' => '2025-02-05 09:45:00'
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'search_term' => 'services en ligne',
                'filters' => json_encode([]),
                'results_count' => 18,
                'created_at' => '2025-02-08 16:20:00',
                'updated_at' => '2025-02-08 16:20:00'
            ],
            [
                'id' => 5,
                'user_id' => 2,
                'search_term' => 'stationnement règlement',
                'filters' => json_encode(['type' => 'reglementation']),
                'results_count' => 7,
                'created_at' => '2025-02-10 11:30:00',
                'updated_at' => '2025-02-10 11:30:00'
            ],
            [
                'id' => 6,
                'user_id' => 5,
                'search_term' => 'certificat naissance',
                'filters' => json_encode(['service' => 'etat_civil']),
                'results_count' => 4,
                'created_at' => '2025-02-12 13:15:00',
                'updated_at' => '2025-02-12 13:15:00'
            ],
            [
                'id' => 7,
                'user_id' => 1,
                'search_term' => 'parc central rénovation',
                'filters' => json_encode(['type' => 'projet']),
                'results_count' => 8,
                'created_at' => '2025-02-15 08:45:00',
                'updated_at' => '2025-02-15 08:45:00'
            ],
            [
                'id' => 8,
                'user_id' => 4,
                'search_term' => 'urbanisme plan',
                'filters' => json_encode(['zone' => 'résidentielle']),
                'results_count' => 15,
                'created_at' => '2025-02-18 15:30:00',
                'updated_at' => '2025-02-18 15:30:00'
            ]
        ]);

        // 9. Public Document Requests
        DB::table('public_document_requests')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'record_id' => 1,
                'request_type' => 'digital',
                'reason' => 'Analyse du budget pour association citoyenne',
                'status' => 'completed',
                'admin_notes' => 'Document envoyé par courriel',
                'processed_at' => '2025-01-25 14:30:00',
                'created_at' => '2025-01-20 10:00:00',
                'updated_at' => '2025-01-25 14:30:00'
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'record_id' => 3,
                'request_type' => 'physical',
                'reason' => 'Soumission pour appel d\'offres',
                'status' => 'approved',
                'admin_notes' => 'Prêt pour récupération',
                'processed_at' => '2025-02-05 11:00:00',
                'created_at' => '2025-02-01 16:30:00',
                'updated_at' => '2025-02-05 11:00:00'
            ],
            [
                'id' => 3,
                'user_id' => 4,
                'record_id' => 2,
                'request_type' => 'digital',
                'reason' => 'Recherche académique sur gouvernance locale',
                'status' => 'completed',
                'admin_notes' => 'Envoyé avec autorisation de citation',
                'processed_at' => '2025-02-10 09:15:00',
                'created_at' => '2025-02-08 13:45:00',
                'updated_at' => '2025-02-10 09:15:00'
            ],
            [
                'id' => 4,
                'user_id' => 5,
                'record_id' => 5,
                'request_type' => 'digital',
                'reason' => 'Projet de développement résidentiel',
                'status' => 'pending',
                'admin_notes' => null,
                'processed_at' => null,
                'created_at' => '2025-02-15 14:20:00',
                'updated_at' => '2025-02-15 14:20:00'
            ],
            [
                'id' => 5,
                'user_id' => 1,
                'record_id' => 4,
                'request_type' => 'physical',
                'reason' => 'Contestation de contravention',
                'status' => 'rejected',
                'admin_notes' => 'Document non disponible pour distribution publique',
                'processed_at' => '2025-02-18 10:30:00',
                'created_at' => '2025-02-16 11:45:00',
                'updated_at' => '2025-02-18 10:30:00'
            ],
            [
                'id' => 6,
                'user_id' => 2,
                'record_id' => 6,
                'request_type' => 'digital',
                'reason' => 'Vérification conformité construction',
                'status' => 'approved',
                'admin_notes' => 'En préparation',
                'processed_at' => '2025-02-20 15:00:00',
                'created_at' => '2025-02-19 09:30:00',
                'updated_at' => '2025-02-20 15:00:00'
            ]
        ]);

        // Continue with remaining tables...
        $this->seedRemainingTables();
    }

    private function seedRemainingTables()
    {
        // 10. Public Responses (Nécessite l'existence de la table 'users')
        DB::table('public_responses')->insert([
            [
                'id' => 1,
                'document_request_id' => 1,
                'responded_by' => 1, // Ajustez selon vos données existantes
                'instructions' => 'Document budget 2025 en format PDF avec annexes financières',
                'status' => 'sent',
                'sent_at' => '2025-01-25 14:30:00',
                'created_at' => '2025-01-25 10:00:00',
                'updated_at' => '2025-01-25 14:30:00'
            ],
            [
                'id' => 2,
                'document_request_id' => 3,
                'responded_by' => 2,
                'instructions' => 'Procès-verbal complet avec autorisation de citation académique',
                'status' => 'sent',
                'sent_at' => '2025-02-10 09:15:00',
                'created_at' => '2025-02-09 16:45:00',
                'updated_at' => '2025-02-10 09:15:00'
            ],
            [
                'id' => 3,
                'document_request_id' => 2,
                'responded_by' => 1,
                'instructions' => 'Documents d\'appel d\'offres disponibles au comptoir',
                'status' => 'sent',
                'sent_at' => '2025-02-05 11:00:00',
                'created_at' => '2025-02-05 09:30:00',
                'updated_at' => '2025-02-05 11:00:00'
            ],
            [
                'id' => 4,
                'document_request_id' => 6,
                'responded_by' => 2,
                'instructions' => 'Compilation des permis avec plans approuvés',
                'status' => 'draft',
                'sent_at' => null,
                'created_at' => '2025-02-20 15:30:00',
                'updated_at' => '2025-02-21 10:15:00'
            ]
        ]);

        // 11. Public Response Attachments (Nécessite l'existence de la table 'attachments')
        DB::table('public_response_attachments')->insert([
            [
                'id' => 1,
                'public_response_id' => 1,
                'attachment_id' => 1, // Ajustez selon vos données existantes
                'download_count' => 3,
                'expires_at' => '2025-12-31 23:59:59',
                'is_public' => true,
                'uploaded_by' => 1,
                'created_at' => '2025-01-25 14:30:00',
                'updated_at' => '2025-02-15 10:20:00'
            ],
            [
                'id' => 2,
                'public_response_id' => 1,
                'attachment_id' => 2,
                'download_count' => 3,
                'expires_at' => '2025-12-31 23:59:59',
                'is_public' => true,
                'uploaded_by' => 1,
                'created_at' => '2025-01-25 14:35:00',
                'updated_at' => '2025-02-15 10:20:00'
            ],
            [
                'id' => 3,
                'public_response_id' => 2,
                'attachment_id' => 3,
                'download_count' => 1,
                'expires_at' => null,
                'is_public' => true,
                'uploaded_by' => 2,
                'created_at' => '2025-02-10 09:15:00',
                'updated_at' => '2025-02-12 14:30:00'
            ],
            [
                'id' => 4,
                'public_response_id' => 3,
                'attachment_id' => 4,
                'download_count' => 0,
                'expires_at' => '2025-05-31 23:59:59',
                'is_public' => false,
                'uploaded_by' => 1,
                'created_at' => '2025-02-05 11:00:00',
                'updated_at' => '2025-02-05 11:00:00'
            ],
            [
                'id' => 5,
                'public_response_id' => 4,
                'attachment_id' => 5,
                'download_count' => 0,
                'expires_at' => '2025-08-31 23:59:59',
                'is_public' => true,
                'uploaded_by' => 2,
                'created_at' => '2025-02-21 10:15:00',
                'updated_at' => '2025-02-21 10:15:00'
            ]
        ]);

        // 12. Public Feedbacks
        DB::table('public_feedbacks')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'subject' => 'Excellente initiative - Portail citoyen',
                'content' => 'Le nouveau portail est très intuitif et facilite grandement les démarches administratives.',
                'status' => 'reviewed',
                'related_id' => null,
                'related_type' => null,
                'rating' => 5,
                'created_at' => '2025-01-25 16:30:00',
                'updated_at' => '2025-02-01 09:15:00'
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'subject' => 'Problème technique lors de l\'inscription',
                'content' => 'J\'ai eu des difficultés à m\'inscrire à l\'assemblée publique via le portail.',
                'status' => 'responded',
                'related_id' => 1,
                'related_type' => 'public_events',
                'rating' => 3,
                'created_at' => '2025-02-08 11:45:00',
                'updated_at' => '2025-02-10 14:20:00'
            ],
            [
                'id' => 3,
                'user_id' => 4,
                'subject' => 'Suggestion d\'amélioration',
                'content' => 'Il serait utile d\'avoir des notifications par SMS pour les événements importants.',
                'status' => 'pending',
                'related_id' => null,
                'related_type' => null,
                'rating' => 4,
                'created_at' => '2025-02-12 09:30:00',
                'updated_at' => '2025-02-12 09:30:00'
            ],
            [
                'id' => 4,
                'user_id' => 5,
                'subject' => 'Délai de traitement trop long',
                'content' => 'Ma demande de document est en attente depuis plus d\'une semaine.',
                'status' => 'pending',
                'related_id' => 4,
                'related_type' => 'public_document_requests',
                'rating' => 2,
                'created_at' => '2025-02-16 14:15:00',
                'updated_at' => '2025-02-16 14:15:00'
            ],
            [
                'id' => 5,
                'user_id' => 1,
                'subject' => 'Félicitations pour la transparence',
                'content' => 'Merci de rendre tous ces documents accessibles facilement.',
                'status' => 'reviewed',
                'related_id' => null,
                'related_type' => null,
                'rating' => 5,
                'created_at' => '2025-02-20 10:45:00',
                'updated_at' => '2025-02-21 08:30:00'
            ]
        ]);

        // 13. Public Chats
        DB::table('public_chats')->insert([
            [
                'id' => 1,
                'title' => 'Support technique',
                'is_group' => false,
                'is_active' => true,
                'created_at' => '2025-02-01 10:00:00',
                'updated_at' => '2025-02-20 15:30:00'
            ],
            [
                'id' => 2,
                'title' => 'Discussion - Parc central',
                'is_group' => true,
                'is_active' => true,
                'created_at' => '2025-02-10 14:30:00',
                'updated_at' => '2025-02-21 09:45:00'
            ],
            [
                'id' => 3,
                'title' => 'Questions budget 2025',
                'is_group' => true,
                'is_active' => true,
                'created_at' => '2025-02-15 11:00:00',
                'updated_at' => '2025-02-19 16:20:00'
            ]
        ]);

        // 14. Public Chat Participants
        DB::table('public_chat_participants')->insert([
            [
                'id' => 1,
                'chat_id' => 1,
                'user_id' => 2,
                'is_admin' => false,
                'last_read_at' => '2025-02-20 15:30:00',
                'created_at' => '2025-02-01 10:00:00',
                'updated_at' => '2025-02-20 15:30:00'
            ],
            [
                'id' => 2,
                'chat_id' => 2,
                'user_id' => 1,
                'is_admin' => true,
                'last_read_at' => '2025-02-21 09:45:00',
                'created_at' => '2025-02-10 14:30:00',
                'updated_at' => '2025-02-21 09:45:00'
            ],
            [
                'id' => 3,
                'chat_id' => 2,
                'user_id' => 4,
                'is_admin' => false,
                'last_read_at' => '2025-02-20 18:20:00',
                'created_at' => '2025-02-10 15:00:00',
                'updated_at' => '2025-02-20 18:20:00'
            ],
            [
                'id' => 4,
                'chat_id' => 2,
                'user_id' => 5,
                'is_admin' => false,
                'last_read_at' => '2025-02-19 14:15:00',
                'created_at' => '2025-02-12 09:30:00',
                'updated_at' => '2025-02-19 14:15:00'
            ],
            [
                'id' => 5,
                'chat_id' => 3,
                'user_id' => 1,
                'is_admin' => true,
                'last_read_at' => '2025-02-19 16:20:00',
                'created_at' => '2025-02-15 11:00:00',
                'updated_at' => '2025-02-19 16:20:00'
            ],
            [
                'id' => 6,
                'chat_id' => 3,
                'user_id' => 2,
                'is_admin' => false,
                'last_read_at' => '2025-02-18 13:45:00',
                'created_at' => '2025-02-16 10:15:00',
                'updated_at' => '2025-02-18 13:45:00'
            ]
        ]);

        // 15. Public Chat Messages
        DB::table('public_chat_messages')->insert([
            [
                'id' => 1,
                'chat_id' => 1,
                'user_id' => 2,
                'message' => 'Bonjour, j\'ai un problème avec l\'upload de documents',
                'is_read' => false,
                'created_at' => '2025-02-01 10:05:00',
                'updated_at' => '2025-02-01 10:05:00'
            ],
            [
                'id' => 2,
                'chat_id' => 2,
                'user_id' => 1,
                'message' => 'Bienvenue dans la discussion sur le parc central!',
                'is_read' => true,
                'created_at' => '2025-02-10 14:35:00',
                'updated_at' => '2025-02-15 09:00:00'
            ],
            [
                'id' => 3,
                'chat_id' => 2,
                'user_id' => 4,
                'message' => 'Merci! J\'ai hâte de partager mes idées pour l\'aire de jeux',
                'is_read' => true,
                'created_at' => '2025-02-10 15:30:00',
                'updated_at' => '2025-02-15 09:00:00'
            ],
            [
                'id' => 4,
                'chat_id' => 2,
                'user_id' => 5,
                'message' => 'Pensez-vous inclure un espace pour les chiens?',
                'is_read' => true,
                'created_at' => '2025-02-12 11:15:00',
                'updated_at' => '2025-02-15 09:00:00'
            ],
            [
                'id' => 5,
                'chat_id' => 2,
                'user_id' => 1,
                'message' => 'Excellente suggestion! C\'est à l\'étude',
                'is_read' => true,
                'created_at' => '2025-02-12 16:45:00',
                'updated_at' => '2025-02-15 09:00:00'
            ],
            [
                'id' => 6,
                'chat_id' => 3,
                'user_id' => 1,
                'message' => 'Cette discussion porte sur le budget 2025 récemment adopté',
                'is_read' => true,
                'created_at' => '2025-02-15 11:15:00',
                'updated_at' => '2025-02-18 10:00:00'
            ],
            [
                'id' => 7,
                'chat_id' => 3,
                'user_id' => 2,
                'message' => 'Où puis-je trouver le détail des investissements en transport?',
                'is_read' => true,
                'created_at' => '2025-02-16 13:30:00',
                'updated_at' => '2025-02-18 10:00:00'
            ],
            [
                'id' => 8,
                'chat_id' => 3,
                'user_id' => 1,
                'message' => 'Je vais vous envoyer le lien vers le document complet',
                'is_read' => true,
                'created_at' => '2025-02-16 14:00:00',
                'updated_at' => '2025-02-18 10:00:00'
            ],
            [
                'id' => 9,
                'chat_id' => 2,
                'user_id' => 4,
                'message' => 'Y aura-t-il une consultation sur site?',
                'is_read' => false,
                'created_at' => '2025-02-19 10:30:00',
                'updated_at' => '2025-02-19 10:30:00'
            ],
            [
                'id' => 10,
                'chat_id' => 1,
                'user_id' => 2,
                'message' => 'Le problème est résolu, merci pour votre aide!',
                'is_read' => false,
                'created_at' => '2025-02-20 15:30:00',
                'updated_at' => '2025-02-20 15:30:00'
            ]
        ]);

        $this->command->info('✅ Toutes les données du portail public ont été insérées avec succès!');
    }
}
