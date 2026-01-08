<?php

namespace Database\Seeders\Records\ExampleData;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Record;
use App\Models\Activity;
use App\Models\RecordStatus;
use App\Models\RecordLevel;
use App\Models\RecordSupport;
use App\Models\Author;
use App\Models\AuthorType;
use App\Models\User;
use Faker\Factory as Faker;

class RecordSeederSimple extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR');

        // Récupérer les IDs des modèles liés
        $activities = Activity::pluck('id')->toArray();
        $statuses = RecordStatus::pluck('id')->toArray();
        $levels = RecordLevel::pluck('id')->toArray();
        $supports = RecordSupport::pluck('id')->toArray();
        $users = User::pluck('id')->toArray();

        // Créer des auteurs de test simples
        $authorType = AuthorType::first();
        $authors = [];

        for ($i = 0; $i < 5; $i++) {
            $authors[] = Author::create([
                'name' => $faker->name(),
                'type_id' => $authorType->id ?? 1,
            ]);
        }

        // Exemples de documents réalistes avec des données courtes
        $recordExamples = [
            [
                'code' => 'CONT-001',
                'name' => 'Accord Maviance SARL',
                'content' => 'Accord de partenariat avec Maviance SARL pour solutions numériques.',
                'date_format' => 'D',
                'date_start' => '2024-01-15',
                'note' => 'Contrat important',
            ],
            [
                'code' => 'CM-003',
                'name' => 'PV Conseil Municipal Mars 2024',
                'content' => 'Procès-verbal conseil municipal du 12 mars 2024.',
                'date_format' => 'D',
                'date_start' => '2024-03-12',
                'note' => 'Document officiel',
            ],
            [
                'code' => 'CORR-045',
                'name' => 'Correspondance Ministère Intérieur',
                'content' => 'Échanges avec Ministère Intérieur sur réforme territoriale.',
                'date_format' => 'M',
                'date_start' => '2024-01-01',
                'date_end' => '2024-06-30',
            ],
            [
                'code' => 'EC-001',
                'name' => 'Registre naissances 2023',
                'content' => 'Registre officiel actes de naissance 2023.',
                'date_format' => 'Y',
                'date_start' => '2023-01-01',
                'date_end' => '2023-12-31',
            ],
            [
                'code' => 'URB-001',
                'name' => 'Plans aménagement quartier Sud',
                'content' => 'Plans et études aménagement quartier Sud.',
                'date_format' => 'Y',
                'date_start' => '2024-01-01',
            ],
        ];

        // Créer les exemples de records
        foreach ($recordExamples as $recordData) {
            $record = Record::create([
                'code' => $recordData['code'],
                'name' => $recordData['name'],
                'content' => $recordData['content'],
                'date_format' => $recordData['date_format'],
                'date_start' => $recordData['date_start'],
                'date_end' => $recordData['date_end'] ?? null,
                'note' => $recordData['note'] ?? null,
                'activity_id' => $faker->randomElement($activities),
                'status_id' => $faker->randomElement($statuses),
                'level_id' => $faker->randomElement($levels),
                'support_id' => $faker->randomElement($supports),
                'user_id' => $faker->randomElement($users),
            ]);

            // Associer un auteur aléatoire
            if (!empty($authors)) {
                $record->authors()->attach($faker->randomElement($authors));
            }
        }

        // Créer des records générés aléatoirement
        for ($i = 0; $i < 20; $i++) {
            $record = Record::create([
                'code' => 'DOC-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => substr($faker->sentence(4), 0, 100), // Limiter la longueur
                'content' => substr($faker->paragraph(3), 0, 500), // Limiter la longueur
                'date_format' => $faker->randomElement(['D', 'M', 'Y']),
                'date_start' => $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
                'date_end' => $faker->optional(0.3)->dateTimeBetween('-1 year', 'now')?->format('Y-m-d'),
                'note' => $faker->optional(0.6)->sentence(8),
                'activity_id' => $faker->randomElement($activities),
                'status_id' => $faker->randomElement($statuses),
                'level_id' => $faker->randomElement($levels),
                'support_id' => $faker->randomElement($supports),
                'user_id' => $faker->randomElement($users),
            ]);

            // Associer un auteur
            if (!empty($authors)) {
                $record->authors()->attach($faker->randomElement($authors));
            }
        }

        $this->command->info('✅ RecordSeederSimple terminé : ' . Record::count() . ' records créés');
    }
}

