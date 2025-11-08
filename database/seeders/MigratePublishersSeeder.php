<?php

namespace Database\Seeders;

use App\Models\RecordBook;
use App\Models\RecordBookPublisher;
use App\Models\RecordBookPublisherSeries;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigratePublishersSeeder extends Seeder
{
    /**
     * Run the migrations to move publisher and series data.
     */
    public function run(): void
    {
        $this->command->info('Starting publisher and series data migration...');

        // Récupérer tous les livres avec publisher non null
        $books = RecordBook::whereNotNull('publisher')->get();
        
        $publishersCreated = 0;
        $seriesCreated = 0;
        $booksUpdated = 0;

        DB::beginTransaction();

        try {
            foreach ($books as $book) {
                // Migrer l'éditeur
                if ($book->publisher) {
                    $publisher = RecordBookPublisher::findOrCreateByName($book->publisher);
                    $book->publisher_id = $publisher->id;
                    
                    if ($publisher->wasRecentlyCreated) {
                        $publishersCreated++;
                    }
                }

                // Migrer la série si elle existe
                if ($book->series && $book->publisher_id) {
                    $series = RecordBookPublisherSeries::findOrCreateBySeries(
                        $book->publisher_id,
                        $book->series,
                        []
                    );
                    $book->series_id = $series->id;

                    if ($series->wasRecentlyCreated) {
                        $seriesCreated++;
                    }

                    // Incrémenter le compteur de volumes
                    $series->incrementVolumeCount();
                }

                // Sauvegarder le livre avec les nouvelles relations
                $book->saveQuietly();
                $booksUpdated++;
            }

            // Mettre à jour les statistiques des éditeurs
            $publishers = RecordBookPublisher::all();
            foreach ($publishers as $publisher) {
                $publisher->updateBookCount();
            }

            // Mettre à jour les compteurs de volumes des séries
            $series = RecordBookPublisherSeries::all();
            foreach ($series as $serie) {
                $serie->updateVolumeCount();
            }

            DB::commit();

            $this->command->info("Migration completed successfully!");
            $this->command->info("- Publishers created: {$publishersCreated}");
            $this->command->info("- Series created: {$seriesCreated}");
            $this->command->info("- Books updated: {$booksUpdated}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Migration failed: " . $e->getMessage());
            throw $e;
        }
    }
}
