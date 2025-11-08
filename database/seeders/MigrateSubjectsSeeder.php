<?php

namespace Database\Seeders;

use App\Models\RecordBook;
use App\Models\RecordSubject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateSubjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Migration des sujets...');

        DB::transaction(function () {
            // Créer les sujets de base
            $subjectsData = [
                ['name' => 'Software Development', 'name_en' => 'Software Development', 'dewey_class' => '005'],
                ['name' => 'Programming', 'name_en' => 'Programming', 'dewey_class' => '005.1'],
                ['name' => 'Best Practices', 'name_en' => 'Best Practices', 'dewey_class' => '005.1'],
                ['name' => 'History', 'name_en' => 'History', 'dewey_class' => '909'],
                ['name' => 'Anthropology', 'name_en' => 'Anthropology', 'dewey_class' => '301'],
                ['name' => 'Human Evolution', 'name_en' => 'Human Evolution', 'dewey_class' => '599.93'],
                ['name' => 'Fiction', 'name_en' => 'Fiction', 'dewey_class' => '813'],
                ['name' => 'Classic Literature', 'name_en' => 'Classic Literature', 'dewey_class' => '810'],
                ['name' => 'American Literature', 'name_en' => 'American Literature', 'dewey_class' => '810'],
                ['name' => 'Physics', 'name_en' => 'Physics', 'dewey_class' => '530'],
                ['name' => 'Science', 'name_en' => 'Science', 'dewey_class' => '500'],
                ['name' => 'Education', 'name_en' => 'Education', 'dewey_class' => '370'],
            ];

            $createdSubjects = 0;
            $updatedBooks = 0;

            foreach ($subjectsData as $subjectData) {
                $subject = RecordSubject::firstOrCreate(
                    ['name' => $subjectData['name']],
                    $subjectData
                );

                $createdSubjects++;
                $this->command->info("✅ Sujet créé: {$subject->name}");
            }

            // Associer les sujets aux livres basé sur les anciens data JSON
            $bookSubjectMap = [
                'Clean Code' => ['Software Development', 'Programming', 'Best Practices'],
                'Sapiens' => ['History', 'Anthropology', 'Human Evolution'],
                'To Kill a Mockingbird' => ['Fiction', 'Classic Literature', 'American Literature'],
                'The Feynman Lectures on Physics' => ['Physics', 'Science', 'Education'],
            ];

            foreach ($bookSubjectMap as $bookTitle => $subjectNames) {
                $book = RecordBook::where('title', 'like', "%{$bookTitle}%")->first();

                if ($book) {
                    foreach ($subjectNames as $index => $subjectName) {
                        $subject = RecordSubject::where('name', $subjectName)->first();

                        if ($subject) {
                            if (!$book->subjects()->where('subject_id', $subject->id)->exists()) {
                                $book->subjects()->attach($subject->id, [
                                    'relevance' => 100,
                                    'is_primary' => ($index === 0), // Premier sujet = principal
                                ]);

                                $updatedBooks++;
                            }
                        }
                    }

                    $this->command->info("📚 Livre mis à jour: {$book->title} avec " . count($subjectNames) . " sujet(s)");
                }
            }

            // Mettre à jour les statistiques
            foreach (RecordSubject::all() as $subject) {
                $subject->updateBookCount();
            }

            $this->command->info("✅ {$createdSubjects} sujets créés");
            $this->command->info("✅ {$updatedBooks} relations livre-sujet créées");
        });

        $this->command->info('✅ Migration terminée!');
    }
}
