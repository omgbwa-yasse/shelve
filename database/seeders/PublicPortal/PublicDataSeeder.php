<?php

namespace Database\Seeders\PublicPortal;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\PublicUser;
use App\Models\PublicNews;
use App\Models\PublicPage;
use App\Models\PublicEvent;
use App\Models\PublicDocumentRequest;
use App\Models\PublicChat;
use App\Models\PublicFeedback;
use App\Models\PublicSearchLog;
use App\Models\User;
use App\Models\RecordPhysical;

class PublicDataSeeder extends Seeder
{
    /**
     * Seed test data for the Public Portal module.
     * Creates public users, news, pages, events, document requests,
     * chats, feedback, and search logs.
     * Idempotent: uses firstOrCreate/updateOrInsert.
     */
    public function run(): void
    {
        $this->command->info('🌐 Seeding Public Portal module test data...');

        $adminUser = User::first();

        // --- 1. Public Users ---
        $puDefs = [
            ['name' => 'BENALI', 'first_name' => 'Karim', 'email' => 'karim.benali@email.dz', 'phone1' => '+213 555 123 456', 'phone2' => '', 'address' => '10 Rue Larbi Ben M\'hidi, Alger', 'is_approved' => true],
            ['name' => 'BOUDIAF', 'first_name' => 'Amina', 'email' => 'amina.boudiaf@email.dz', 'phone1' => '+213 555 234 567', 'phone2' => '', 'address' => '25 Boulevard Che Guevara, Oran', 'is_approved' => true],
            ['name' => 'MEBARKI', 'first_name' => 'Youcef', 'email' => 'youcef.mebarki@email.dz', 'phone1' => '+213 555 345 678', 'phone2' => '+213 555 345 679', 'address' => '5 Rue de Constantine', 'is_approved' => true],
            ['name' => 'ZERHOUNI', 'first_name' => 'Fatima', 'email' => 'fatima.zerhouni@email.dz', 'phone1' => '+213 555 456 789', 'phone2' => '', 'address' => '12 Avenue de l\'ALN, Annaba', 'is_approved' => false],
            ['name' => 'CHERIF', 'first_name' => 'Omar', 'email' => 'omar.cherif@email.dz', 'phone1' => '+213 555 567 890', 'phone2' => '', 'address' => '8 Rue des Frères Bouadou, Tizi Ouzou', 'is_approved' => false],
        ];

        $publicUsers = [];
        foreach ($puDefs as $pu) {
            $publicUsers[] = PublicUser::firstOrCreate(
                ['email' => $pu['email']],
                array_merge($pu, [
                    'password' => Hash::make('password123'),
                    'preferences' => json_encode(['language' => 'fr', 'notifications' => true]),
                ])
            );
        }

        // --- 2. Public News ---
        $newsDefs = [
            ['title' => 'Inauguration de la nouvelle salle de lecture', 'slug' => 'inauguration-salle-lecture', 'status' => 'published', 'featured' => true, 'summary' => 'La nouvelle salle de lecture moderne accueille désormais les chercheurs.'],
            ['title' => 'Exposition : Archives de l\'Indépendance', 'slug' => 'exposition-archives-independance', 'status' => 'published', 'featured' => true, 'summary' => 'Une exposition exceptionnelle présentant des documents inédits de 1954-1962.'],
            ['title' => 'Horaires d\'été 2026', 'slug' => 'horaires-ete-2026', 'status' => 'published', 'featured' => false, 'summary' => 'Les horaires d\'ouverture changent à partir du 1er juin.'],
            ['title' => 'Atelier de numérisation participative', 'slug' => 'atelier-numerisation', 'status' => 'draft', 'featured' => false, 'summary' => 'Participez à la numérisation de nos collections photographiques.'],
            ['title' => 'Fermeture exceptionnelle le 5 novembre', 'slug' => 'fermeture-5-novembre', 'status' => 'archived', 'featured' => false, 'summary' => 'Le centre d\'archives sera fermé pour travaux de maintenance.'],
        ];

        foreach ($newsDefs as $nd) {
            PublicNews::firstOrCreate(
                ['slug' => $nd['slug']],
                [
                    'title' => $nd['title'],
                    'name' => $nd['title'],
                    'content' => "<p>{$nd['summary']}</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>",
                    'summary' => $nd['summary'],
                    'status' => $nd['status'],
                    'featured' => $nd['featured'],
                    'author_id' => $adminUser?->id,
                    'published_at' => $nd['status'] === 'published' ? now()->subDays(rand(1, 30)) : null,
                ]
            );
        }

        // --- 3. Public Pages (hierarchy) ---
        $pageAbout = PublicPage::firstOrCreate(
            ['slug' => 'a-propos'],
            ['title' => 'À propos', 'name' => 'À propos', 'content' => '<p>Présentation du centre d\'archives national.</p>', 'meta_description' => 'À propos du centre d\'archives', 'status' => 'published', 'is_published' => true, 'author_id' => $adminUser?->id, 'order' => 1]
        );

        PublicPage::firstOrCreate(
            ['slug' => 'histoire'],
            ['title' => 'Notre histoire', 'name' => 'Notre histoire', 'content' => '<p>Historique de l\'institution depuis sa création.</p>', 'meta_description' => 'Histoire du centre d\'archives', 'status' => 'published', 'is_published' => true, 'author_id' => $adminUser?->id, 'order' => 1, 'parent_id' => $pageAbout->id]
        );

        PublicPage::firstOrCreate(
            ['slug' => 'equipe'],
            ['title' => 'Notre équipe', 'name' => 'Notre équipe', 'content' => '<p>Présentation de l\'équipe scientifique et technique.</p>', 'meta_description' => 'Équipe du centre d\'archives', 'status' => 'published', 'is_published' => true, 'author_id' => $adminUser?->id, 'order' => 2, 'parent_id' => $pageAbout->id]
        );

        $pageServices = PublicPage::firstOrCreate(
            ['slug' => 'services'],
            ['title' => 'Services', 'name' => 'Services', 'content' => '<p>Découvrez les services proposés par le centre d\'archives.</p>', 'meta_description' => 'Services du centre d\'archives', 'status' => 'published', 'is_published' => true, 'author_id' => $adminUser?->id, 'order' => 2]
        );

        PublicPage::firstOrCreate(
            ['slug' => 'consultation'],
            ['title' => 'Consultation sur place', 'name' => 'Consultation', 'content' => '<p>Modalités de consultation des documents en salle de lecture.</p>', 'status' => 'published', 'is_published' => true, 'author_id' => $adminUser?->id, 'order' => 1, 'parent_id' => $pageServices->id]
        );

        PublicPage::firstOrCreate(
            ['slug' => 'reproductions'],
            ['title' => 'Demande de reproductions', 'name' => 'Reproductions', 'content' => '<p>Comment demander des reproductions de documents d\'archives.</p>', 'status' => 'draft', 'is_published' => false, 'author_id' => $adminUser?->id, 'order' => 2, 'parent_id' => $pageServices->id]
        );

        // --- 4. Public Events ---
        $eventDefs = [
            ['name' => 'Journée portes ouvertes', 'description' => 'Visite guidée des magasins de conservation et des ateliers de restauration.', 'start_date' => now()->addDays(15), 'end_date' => now()->addDays(15)->addHours(6), 'location' => 'Centre d\'archives national', 'is_online' => false],
            ['name' => 'Webinaire : Introduction à la recherche généalogique', 'description' => 'Comment utiliser les archives pour retrouver ses ancêtres.', 'start_date' => now()->addDays(30), 'end_date' => now()->addDays(30)->addHours(2), 'location' => null, 'is_online' => true],
            ['name' => 'Conférence : La conservation préventive', 'description' => 'Les bonnes pratiques de conservation préventive des documents.', 'start_date' => now()->subDays(10), 'end_date' => now()->subDays(10)->addHours(3), 'location' => 'Auditorium', 'is_online' => false],
        ];

        $createdEvents = [];
        foreach ($eventDefs as $ed) {
            $createdEvents[] = PublicEvent::firstOrCreate(
                ['name' => $ed['name']],
                $ed
            );
        }

        // Event registrations
        if (!empty($publicUsers) && !empty($createdEvents)) {
            foreach ($publicUsers as $i => $pu) {
                if ($i >= count($createdEvents)) break;
                DB::table('public_event_registrations')->updateOrInsert(
                    ['user_id' => $pu->id, 'event_id' => $createdEvents[$i]->id],
                    ['status' => 'confirmed', 'registered_at' => now(), 'notes' => null, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // --- 5. Public Document Requests ---
        $publicRecords = DB::table('public_records')->take(3)->get();
        if ($publicRecords->isNotEmpty() && !empty($publicUsers)) {
            $reqDefs = [
                ['user_idx' => 0, 'type' => 'physical', 'reason' => 'Recherche généalogique — famille BENALI.', 'status' => 'pending'],
                ['user_idx' => 1, 'type' => 'digital', 'reason' => 'Étude universitaire sur l\'histoire locale.', 'status' => 'approved', 'admin_notes' => 'Demande validée. Documents disponibles en format PDF.'],
                ['user_idx' => 2, 'type' => 'physical', 'reason' => 'Consultation à titre professionnel.', 'status' => 'completed', 'admin_notes' => 'Documents communiqués le 15/06/2026.', 'processed_at' => now()->subDays(5)],
            ];

            foreach ($reqDefs as $rd) {
                if (isset($publicUsers[$rd['user_idx']]) && $publicRecords->count() > 0) {
                    $rec = $publicRecords[$rd['user_idx'] % $publicRecords->count()];
                    PublicDocumentRequest::firstOrCreate(
                        ['user_id' => $publicUsers[$rd['user_idx']]->id, 'record_id' => $rec->id, 'request_type' => $rd['type']],
                        [
                            'reason' => $rd['reason'],
                            'status' => $rd['status'],
                            'admin_notes' => $rd['admin_notes'] ?? null,
                            'processed_at' => $rd['processed_at'] ?? null,
                        ]
                    );
                }
            }
        }

        // --- 6. Public Chat ---
        $chat1 = PublicChat::firstOrCreate(
            ['title' => 'Aide à la recherche'],
            ['is_group' => true, 'is_active' => true]
        );
        $chat2 = PublicChat::firstOrCreate(
            ['title' => null, 'is_group' => false],
            ['is_active' => true]
        );

        // Chat participants
        if (!empty($publicUsers)) {
            foreach ([$publicUsers[0], $publicUsers[1], $publicUsers[2]] as $idx => $pu) {
                DB::table('public_chat_participants')->updateOrInsert(
                    ['chat_id' => $chat1->id, 'user_id' => $pu->id],
                    ['is_admin' => ($idx === 0), 'last_read_at' => now(), 'created_at' => now(), 'updated_at' => now()]
                );
            }
            // Private chat
            DB::table('public_chat_participants')->updateOrInsert(
                ['chat_id' => $chat2->id, 'user_id' => $publicUsers[0]->id],
                ['is_admin' => false, 'last_read_at' => now(), 'created_at' => now(), 'updated_at' => now()]
            );
            DB::table('public_chat_participants')->updateOrInsert(
                ['chat_id' => $chat2->id, 'user_id' => $publicUsers[1]->id],
                ['is_admin' => false, 'last_read_at' => now(), 'created_at' => now(), 'updated_at' => now()]
            );

            // Chat messages
            $messages = [
                ['chat_id' => $chat1->id, 'user_id' => $publicUsers[0]->id, 'content' => 'Bonjour, je cherche des archives sur la région de Tlemcen.'],
                ['chat_id' => $chat1->id, 'user_id' => $publicUsers[1]->id, 'content' => 'Avez-vous essayé la recherche par mot-clé sur le portail ?'],
                ['chat_id' => $chat1->id, 'user_id' => $publicUsers[0]->id, 'content' => 'Oui, mais je ne trouve pas de résultats pertinents.'],
                ['chat_id' => $chat2->id, 'user_id' => $publicUsers[0]->id, 'content' => 'Pouvez-vous m\'aider avec ma demande de reproduction ?'],
                ['chat_id' => $chat2->id, 'user_id' => $publicUsers[1]->id, 'content' => 'Bien sûr, envoyez-moi la référence du document.'],
            ];

            foreach ($messages as $idx => $msg) {
                DB::table('public_chat_messages')->updateOrInsert(
                    ['chat_id' => $msg['chat_id'], 'user_id' => $msg['user_id'], 'content' => $msg['content']],
                    ['created_at' => now()->subMinutes(count($messages) - $idx), 'updated_at' => now()]
                );
            }
        }

        // --- 7. Public Feedback ---
        $feedbackDefs = [
            ['title' => 'Excellent service de consultation', 'content' => 'Le personnel est très compétent et a facilité mes recherches.', 'type' => 'improvement', 'priority' => 'low', 'status' => 'resolved', 'rating' => 5, 'user_idx' => 0],
            ['title' => 'Bug affichage sur mobile', 'content' => 'La page de résultats ne s\'affiche pas correctement sur smartphone.', 'type' => 'bug', 'priority' => 'high', 'status' => 'in_progress', 'rating' => 2, 'user_idx' => 1],
            ['title' => 'Suggestion : filtre par date', 'content' => 'Il serait utile de pouvoir filtrer les résultats par plage de dates.', 'type' => 'feature', 'priority' => 'medium', 'status' => 'new', 'rating' => null, 'user_idx' => 2],
            ['title' => 'Problème d\'accès aux numérisations', 'content' => 'Impossible d\'ouvrir les PDF des documents numérisés.', 'type' => 'bug', 'priority' => 'high', 'status' => 'new', 'rating' => 1, 'contact_name' => 'Visiteur anonyme', 'contact_email' => 'anonyme@test.dz'],
        ];

        foreach ($feedbackDefs as $fd) {
            $userId = isset($fd['user_idx'], $publicUsers[$fd['user_idx']]) ? $publicUsers[$fd['user_idx']]->id : null;
            PublicFeedback::firstOrCreate(
                ['title' => $fd['title']],
                [
                    'user_id' => $userId,
                    'content' => $fd['content'],
                    'type' => $fd['type'],
                    'priority' => $fd['priority'],
                    'status' => $fd['status'],
                    'rating' => $fd['rating'],
                    'contact_name' => $fd['contact_name'] ?? null,
                    'contact_email' => $fd['contact_email'] ?? null,
                ]
            );
        }

        // --- 8. Public Search Logs ---
        $searchDefs = [
            ['term' => 'archives guerre indépendance', 'filters' => ['date_range' => '1954-1962', 'type' => 'physical'], 'results' => 42],
            ['term' => 'état civil Tlemcen', 'filters' => ['location' => 'Tlemcen'], 'results' => 15],
            ['term' => 'budget 2022', 'filters' => [], 'results' => 3],
            ['term' => 'dahir foncier', 'filters' => ['type' => 'digital'], 'results' => 0],
            ['term' => 'correspondance officielle', 'filters' => ['date_range' => '1980-2000'], 'results' => 128],
        ];

        foreach ($searchDefs as $idx => $sd) {
            $userId = isset($publicUsers[$idx]) ? $publicUsers[$idx]->id : ($publicUsers[0]->id ?? null);
            if ($userId) {
                PublicSearchLog::firstOrCreate(
                    ['user_id' => $userId, 'search_term' => $sd['term']],
                    [
                        'filters' => $sd['filters'],
                        'results_count' => $sd['results'],
                    ]
                );
            }
        }

        $this->command->info('✅ Public Portal: ' . count($publicUsers) . ' users, 5 news, 6 pages, ' . count($createdEvents) . ' events, 2 chats, ' . count($feedbackDefs) . ' feedbacks seeded.');
    }
}
