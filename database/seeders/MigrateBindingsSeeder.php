<?php

namespace Database\Seeders;

use App\Models\RecordBook;
use App\Models\RecordBookBinding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateBindingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🔄 Migration des reliures...\n\n";

        DB::beginTransaction();

        try {
            // Common book bindings
            $bindings = [
                [
                    'name' => 'Broché',
                    'name_en' => 'Paperback',
                    'description' => 'Reliure souple avec couverture en papier épais',
                    'category' => 'soft',
                    'durability_rating' => 5,
                    'relative_cost' => 1.0,
                    'status' => 'active',
                ],
                [
                    'name' => 'Relié',
                    'name_en' => 'Hardcover',
                    'description' => 'Reliure rigide avec couverture cartonnée',
                    'category' => 'hard',
                    'durability_rating' => 9,
                    'relative_cost' => 1.8,
                    'status' => 'active',
                ],
                [
                    'name' => 'Relié toilé',
                    'name_en' => 'Cloth binding',
                    'description' => 'Reliure rigide avec couverture en tissu',
                    'category' => 'hard',
                    'durability_rating' => 8,
                    'relative_cost' => 1.6,
                    'status' => 'active',
                ],
                [
                    'name' => 'Relié cuir',
                    'name_en' => 'Leather binding',
                    'description' => 'Reliure de luxe en cuir véritable',
                    'category' => 'hard',
                    'durability_rating' => 10,
                    'relative_cost' => 3.0,
                    'status' => 'active',
                ],
                [
                    'name' => 'Spirale',
                    'name_en' => 'Spiral binding',
                    'description' => 'Reliure à spirale métallique ou plastique',
                    'category' => 'spiral',
                    'durability_rating' => 4,
                    'relative_cost' => 0.8,
                    'status' => 'active',
                ],
                [
                    'name' => 'Agrafé',
                    'name_en' => 'Stapled',
                    'description' => 'Reliure par agrafes (brochures, magazines)',
                    'category' => 'stapled',
                    'durability_rating' => 3,
                    'relative_cost' => 0.5,
                    'status' => 'active',
                ],
                [
                    'name' => 'Dos carré collé',
                    'name_en' => 'Perfect binding',
                    'description' => 'Reliure avec dos plat et pages collées',
                    'category' => 'soft',
                    'durability_rating' => 6,
                    'relative_cost' => 1.1,
                    'status' => 'active',
                ],
            ];

            foreach ($bindings as $bindingData) {
                $binding = RecordBookBinding::firstOrCreate(
                    ['name' => $bindingData['name']],
                    $bindingData
                );
                echo "✅ Reliure créée: {$binding->full_description}\n";
            }

            echo "\n";

            // Migrate existing books - assign paperback binding
            $books = RecordBook::withTrashed()->get();
            $updatedCount = 0;
            $paperback = RecordBookBinding::where('name', 'Broché')->first();

            foreach ($books as $book) {
                if ($paperback) {
                    $book->binding_id = $paperback->id;
                    $book->save();
                    $updatedCount++;
                    echo "📚 Livre mis à jour: {$book->title} → {$paperback->name}\n";
                }
            }

            // Update book counts for all bindings
            foreach (RecordBookBinding::all() as $binding) {
                $binding->updateBookCount();
            }

            DB::commit();

            echo "\n✅ " . count($bindings) . " reliures créées\n";
            echo "✅ {$updatedCount} livres mis à jour\n";
            echo "✅ Migration terminée!\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "❌ Erreur: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
