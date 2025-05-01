<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BulletinBoardSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Bulletin Boards
        $bulletinBoards = [
            [
                'id' => 1,
                'name' => 'Annonces Générales',
                'description' => 'Tableau d\'affichage des annonces générales',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'Événements Archives',
                'description' => 'Événements liés aux archives',
                'created_by' => 5,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Informations RH',
                'description' => 'Informations des ressources humaines',
                'created_by' => 3,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'Projets en cours',
                'description' => 'Suivi des projets en cours',
                'created_by' => 2,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ];

        // Bulletin Board Organization
        $bulletinBoardOrganisations = [
            [
                'id' => 1,
                'bulletin_board_id' => 1,
                'organisation_id' => 1,
                'assigned_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'bulletin_board_id' => 2,
                'organisation_id' => 5,
                'assigned_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'bulletin_board_id' => 3,
                'organisation_id' => 3,
                'assigned_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'bulletin_board_id' => 4,
                'organisation_id' => 2,
                'assigned_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Bulletin Board Users
        $bulletinBoardUsers = [
            [
                'id' => 1,
                'bulletin_board_id' => 1,
                'user_id' => 1,
                'role' => 'super_admin',
                'permissions' => 'write',
                'assigned_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'bulletin_board_id' => 1,
                'user_id' => 2,
                'role' => 'admin',
                'permissions' => 'write',
                'assigned_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'bulletin_board_id' => 2,
                'user_id' => 5,
                'role' => 'admin',
                'permissions' => 'write',
                'assigned_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'bulletin_board_id' => 3,
                'user_id' => 3,
                'role' => 'admin',
                'permissions' => 'write',
                'assigned_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'bulletin_board_id' => 4,
                'user_id' => 2,
                'role' => 'admin',
                'permissions' => 'write',
                'assigned_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ];

        // Events
        $events = [];

        // Create events for each bulletin board
        for ($boardId = 1; $boardId <= 4; $boardId++) {
            for ($i = 1; $i <= 3; $i++) {
                $startDate = Carbon::now()->addDays(rand(5, 30));
                $endDate = $startDate->copy()->addHours(rand(1, 8));

                // FIX: Add parentheses around nested ternary
                $status = $i == 1 ? 'published' : ($i == 2 ? 'draft' : (rand(0, 1) ? 'published' : 'draft'));

                $events[] = [
                    'id' => ($boardId - 1) * 3 + $i,
                    'bulletin_board_id' => $boardId,
                    'name' => $this->getEventName($boardId, $i),
                    'description' => $this->getEventDescription($boardId, $i),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'location' => $this->getEventLocation($i),
                    'status' => $status,
                    'created_by' => $boardId == 1 ? 1 : ($boardId == 2 ? 5 : ($boardId == 3 ? 3 : 2)),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];
            }
        }

        // Posts
        $posts = [];

        // Create posts for each bulletin board
        for ($boardId = 1; $boardId <= 4; $boardId++) {
            for ($i = 1; $i <= 5; $i++) {
                $startDate = Carbon::now()->subDays(rand(0, 5));
                $endDate = $startDate->copy()->addDays(rand(10, 30));

                // FIX: Add parentheses around nested ternary
                $status = $i <= 3 ? 'published' : ($i == 4 ? 'draft' : (rand(0, 1) ? 'published' : 'draft'));

                $posts[] = [
                    'id' => ($boardId - 1) * 5 + $i,
                    'bulletin_board_id' => $boardId,
                    'name' => $this->getPostName($boardId, $i),
                    'description' => $this->getPostDescription($boardId, $i),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status,
                    'created_by' => $boardId == 1 ? 1 : ($boardId == 2 ? 5 : ($boardId == 3 ? 3 : 2)),
                    'created_at' => $startDate,
                    'updated_at' => $startDate,
                    'deleted_at' => null,
                ];
            }
        }

        DB::table('bulletin_boards')->insert($bulletinBoards);
        DB::table('bulletin_board_organisation')->insert($bulletinBoardOrganisations);
        DB::table('bulletin_board_user')->insert($bulletinBoardUsers);
        DB::table('events')->insert($events);
        DB::table('posts')->insert($posts);
    }

    private function getEventName($boardId, $i)
    {
        $events = [
            1 => [
                'Réunion générale annuelle',
                'Session d\'information - Nouveaux outils',
                'Formation obligatoire - Sécurité des données',
            ],
            2 => [
                'Visite des Archives Nationales',
                'Atelier - Conservation préventive',
                'Conférence - Numérisation des archives',
            ],
            3 => [
                'Session d\'information - Nouveaux avantages',
                'Entretiens annuels - Planning',
                'Atelier - Gestion du stress',
            ],
            4 => [
                'Revue de projet trimestrielle',
                'Présentation - Nouveaux projets',
                'Réunion des chefs de projet',
            ],
        ];

        return $events[$boardId][$i-1] ?? 'Événement ' . $i;
    }

    private function getEventDescription($boardId, $i)
    {
        $descriptions = [
            1 => 'Présentation de la stratégie annuelle et des objectifs. Tous les membres du personnel sont invités à participer.',
            2 => 'Présentation des dernières pratiques et techniques en matière d\'archivage. Formation assurée par des experts.',
            3 => 'Informations importantes concernant les ressources humaines et le personnel. Participation obligatoire.',
            4 => 'Suivi des projets en cours, présentation des avancées et discussion des problèmes rencontrés.',
        ];

        return $descriptions[$boardId] . ' (Événement #' . $i . ' du tableau ' . $boardId . ')';
    }

    private function getEventLocation($i)
    {
        $locations = [
            'Salle de conférence - Bâtiment principal',
            'Salle de réunion A - 1er étage',
            'Amphithéâtre - Bâtiment annexe',
            'Salle de formation - Rez-de-chaussée',
            'Espace collaboratif - 2ème étage',
        ];

        return $locations[$i % count($locations)];
    }

    private function getPostName($boardId, $i)
    {
        $prefixes = [
            1 => 'Annonce : ',
            2 => 'Archives : ',
            3 => 'RH : ',
            4 => 'Projet : ',
        ];

        $subjects = [
            'Nouveaux horaires d\'ouverture',
            'Mise à jour des procédures',
            'Information importante',
            'Rappel - Échéances à venir',
            'Félicitations équipe projet',
            'Nomination nouvelle direction',
            'Changement de locaux',
            'Perturbation système informatique',
            'Nouveaux documents disponibles',
            'Formations disponibles',
        ];

        return $prefixes[$boardId] . $subjects[$i % count($subjects)];
    }

    private function getPostDescription($boardId, $i)
    {
        $descriptions = [
            1 => 'Information générale concernant l\'ensemble des services. Veuillez prendre connaissance de ces informations et les appliquer.',
            2 => 'Information concernant le service des archives. Mise à jour des procédures et des bonnes pratiques.',
            3 => 'Information importante des ressources humaines concernant le personnel et l\'organisation du travail.',
            4 => 'Information sur l\'avancement des projets et les prochaines étapes. À consulter par toutes les équipes concernées.',
        ];

        return $descriptions[$boardId] . ' (Post #' . $i . ' du tableau ' . $boardId . ')';
    }
}
