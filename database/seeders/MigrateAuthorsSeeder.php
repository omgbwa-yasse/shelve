<?php

namespace Database\Seeders;

use App\Models\RecordBook;
use App\Models\RecordAuthor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateAuthorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Migration des auteurs...');

        DB::transaction(function () {
            // Récupérer tous les livres avec leurs anciennes données d'auteurs
            // Depuis que record_book_authors a été supprimée, nous allons créer des auteurs de test

            $authorsData = [
                [
                    'name' => 'Robert C. Martin',
                    'nationality' => 'US',
                    'biography' => 'Software engineer and author known for Clean Code and SOLID principles.',
                ],
                [
                    'name' => 'Yuval Noah Harari',
                    'nationality' => 'IL',
                    'birth_year' => 1976,
                    'biography' => 'Israeli historian and author of Sapiens and Homo Deus.',
                ],
                [
                    'name' => 'Harper Lee',
                    'nationality' => 'US',
                    'birth_year' => 1926,
                    'death_year' => 2016,
                    'status' => 'deceased',
                    'biography' => 'American novelist best known for To Kill a Mockingbird.',
                ],
                [
                    'name' => 'Richard P. Feynman',
                    'nationality' => 'US',
                    'birth_year' => 1918,
                    'death_year' => 1988,
                    'status' => 'deceased',
                    'biography' => 'American theoretical physicist and Nobel Prize winner.',
                ],
            ];

            $createdAuthors = 0;
            $updatedBooks = 0;

            foreach ($authorsData as $authorData) {
                $author = RecordAuthor::findOrCreateByName($authorData['name'], [
                    'nationality' => $authorData['nationality'] ?? null,
                    'birth_year' => $authorData['birth_year'] ?? null,
                    'death_year' => $authorData['death_year'] ?? null,
                    'status' => $authorData['status'] ?? 'active',
                    'biography' => $authorData['biography'] ?? null,
                ]);

                $createdAuthors++;

                $this->command->info("✅ Auteur créé: {$author->full_name}");
            }

            // Associer les auteurs aux livres
            $bookAuthorMap = [
                'Clean Code' => ['Robert C. Martin'],
                'Sapiens' => ['Yuval Noah Harari'],
                'To Kill a Mockingbird' => ['Harper Lee'],
                'The Feynman Lectures on Physics' => ['Richard P. Feynman'],
            ];

            foreach ($bookAuthorMap as $bookTitle => $authorNames) {
                $book = RecordBook::where('title', 'like', "%{$bookTitle}%")->first();

                if ($book) {
                    foreach ($authorNames as $index => $authorName) {
                        $author = RecordAuthor::where('full_name', $authorName)->first();

                        if ($author) {
                            // Vérifier si la relation n'existe pas déjà
                            if (!$book->authors()->where('author_id', $author->id)->exists()) {
                                $book->authors()->attach($author->id, [
                                    'role' => 'author',
                                    'display_order' => $index + 1,
                                ]);

                                $updatedBooks++;
                            }
                        }
                    }

                    $this->command->info("📚 Livre mis à jour: {$book->title}");
                }
            }

            // Mettre à jour les statistiques des auteurs
            foreach (RecordAuthor::all() as $author) {
                $author->updateBookCount();
            }

            $this->command->info("✅ {$createdAuthors} auteurs créés");
            $this->command->info("✅ {$updatedBooks} relations livre-auteur créées");
        });

        $this->command->info('✅ Migration terminée!');
    }
}
