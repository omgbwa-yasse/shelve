<?php

namespace Database\Seeders;

use App\Models\RecordBook;
use App\Models\RecordBookFormat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateFormatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🔄 Migration des formats de livre...\n\n";

        DB::beginTransaction();

        try {
            // Common book formats with dimensions
            $formats = [
                [
                    'name' => 'Poche',
                    'name_en' => 'Pocket',
                    'description' => 'Format poche standard',
                    'category' => 'pocket',
                    'width_cm' => 11.0,
                    'height_cm' => 18.0,
                    'dimensions_range' => '11 × 18 cm',
                    'status' => 'active',
                ],
                [
                    'name' => 'In-12',
                    'name_en' => 'Duodecimo',
                    'description' => 'Format in-douze (1/12 de feuille)',
                    'category' => 'pocket',
                    'width_cm' => 12.0,
                    'height_cm' => 19.0,
                    'dimensions_range' => '12 × 19 cm',
                    'status' => 'active',
                ],
                [
                    'name' => 'In-8',
                    'name_en' => 'Octavo',
                    'description' => 'Format in-octavo (1/8 de feuille)',
                    'category' => 'standard',
                    'width_cm' => 15.0,
                    'height_cm' => 23.0,
                    'dimensions_range' => '15 × 23 cm',
                    'status' => 'active',
                ],
                [
                    'name' => 'A5',
                    'name_en' => 'A5',
                    'description' => 'Format ISO A5',
                    'category' => 'standard',
                    'width_cm' => 14.8,
                    'height_cm' => 21.0,
                    'dimensions_range' => '14.8 × 21 cm (ISO 216)',
                    'status' => 'active',
                ],
                [
                    'name' => 'In-4',
                    'name_en' => 'Quarto',
                    'description' => 'Format in-quarto (1/4 de feuille)',
                    'category' => 'large',
                    'width_cm' => 21.0,
                    'height_cm' => 27.0,
                    'dimensions_range' => '21 × 27 cm',
                    'status' => 'active',
                ],
                [
                    'name' => 'A4',
                    'name_en' => 'A4',
                    'description' => 'Format ISO A4',
                    'category' => 'large',
                    'width_cm' => 21.0,
                    'height_cm' => 29.7,
                    'dimensions_range' => '21 × 29.7 cm (ISO 216)',
                    'status' => 'active',
                ],
                [
                    'name' => 'In-folio',
                    'name_en' => 'Folio',
                    'description' => 'Format in-folio (1/2 de feuille)',
                    'category' => 'folio',
                    'width_cm' => 30.0,
                    'height_cm' => 40.0,
                    'dimensions_range' => '30 × 40 cm',
                    'status' => 'active',
                ],
                [
                    'name' => 'Grand format',
                    'name_en' => 'Large format',
                    'description' => 'Grand format (beau livre)',
                    'category' => 'large',
                    'width_cm' => 24.0,
                    'height_cm' => 30.0,
                    'dimensions_range' => '24 × 30 cm ou plus',
                    'status' => 'active',
                ],
            ];

            foreach ($formats as $formatData) {
                $format = RecordBookFormat::firstOrCreate(
                    ['name' => $formatData['name']],
                    $formatData
                );
                echo "✅ Format créé: {$format->display_name}";
                if ($format->formatted_dimensions) {
                    echo " - {$format->formatted_dimensions}";
                }
                if ($format->surface_area) {
                    echo " ({$format->surface_area} cm²)";
                }
                echo "\n";
            }

            echo "\n";

            // Migrate existing books - assign standard format (In-8)
            $books = RecordBook::withTrashed()->get();
            $updatedCount = 0;
            $standardFormat = RecordBookFormat::where('name', 'In-8')->first();

            foreach ($books as $book) {
                if ($standardFormat) {
                    $book->format_id = $standardFormat->id;
                    $book->save();
                    $updatedCount++;
                    echo "📚 Livre mis à jour: {$book->title} → {$standardFormat->name}\n";
                }
            }

            // Update book counts for all formats
            foreach (RecordBookFormat::all() as $format) {
                $format->updateBookCount();
            }

            DB::commit();

            echo "\n✅ " . count($formats) . " formats créés\n";
            echo "✅ {$updatedCount} livres mis à jour\n";
            echo "✅ Migration terminée!\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "❌ Erreur: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
