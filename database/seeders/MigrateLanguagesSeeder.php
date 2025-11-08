<?php

namespace Database\Seeders;

use App\Models\RecordBook;
use App\Models\RecordLanguage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateLanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🔄 Migration des langues...\n\n";

        DB::beginTransaction();

        try {
            // Common languages with full data
            $languages = [
                [
                    'code' => 'fr',
                    'name' => 'Français',
                    'name_en' => 'French',
                    'native_name' => 'Français',
                    'script' => 'Latin',
                    'direction' => 'ltr',
                    'iso_639_1' => 'fr',
                    'iso_639_2' => 'fre',
                    'iso_639_3' => 'fra',
                    'status' => 'active',
                ],
                [
                    'code' => 'en',
                    'name' => 'Anglais',
                    'name_en' => 'English',
                    'native_name' => 'English',
                    'script' => 'Latin',
                    'direction' => 'ltr',
                    'iso_639_1' => 'en',
                    'iso_639_2' => 'eng',
                    'iso_639_3' => 'eng',
                    'status' => 'active',
                ],
                [
                    'code' => 'es',
                    'name' => 'Espagnol',
                    'name_en' => 'Spanish',
                    'native_name' => 'Español',
                    'script' => 'Latin',
                    'direction' => 'ltr',
                    'iso_639_1' => 'es',
                    'iso_639_2' => 'spa',
                    'iso_639_3' => 'spa',
                    'status' => 'active',
                ],
                [
                    'code' => 'de',
                    'name' => 'Allemand',
                    'name_en' => 'German',
                    'native_name' => 'Deutsch',
                    'script' => 'Latin',
                    'direction' => 'ltr',
                    'iso_639_1' => 'de',
                    'iso_639_2' => 'ger',
                    'iso_639_3' => 'deu',
                    'status' => 'active',
                ],
                [
                    'code' => 'it',
                    'name' => 'Italien',
                    'name_en' => 'Italian',
                    'native_name' => 'Italiano',
                    'script' => 'Latin',
                    'direction' => 'ltr',
                    'iso_639_1' => 'it',
                    'iso_639_2' => 'ita',
                    'iso_639_3' => 'ita',
                    'status' => 'active',
                ],
                [
                    'code' => 'pt',
                    'name' => 'Portugais',
                    'name_en' => 'Portuguese',
                    'native_name' => 'Português',
                    'script' => 'Latin',
                    'direction' => 'ltr',
                    'iso_639_1' => 'pt',
                    'iso_639_2' => 'por',
                    'iso_639_3' => 'por',
                    'status' => 'active',
                ],
                [
                    'code' => 'ar',
                    'name' => 'Arabe',
                    'name_en' => 'Arabic',
                    'native_name' => 'العربية',
                    'script' => 'Arabic',
                    'direction' => 'rtl',
                    'iso_639_1' => 'ar',
                    'iso_639_2' => 'ara',
                    'iso_639_3' => 'ara',
                    'status' => 'active',
                ],
                [
                    'code' => 'zh',
                    'name' => 'Chinois',
                    'name_en' => 'Chinese',
                    'native_name' => '中文',
                    'script' => 'Han',
                    'direction' => 'ltr',
                    'iso_639_1' => 'zh',
                    'iso_639_2' => 'chi',
                    'iso_639_3' => 'zho',
                    'status' => 'active',
                ],
                [
                    'code' => 'ja',
                    'name' => 'Japonais',
                    'name_en' => 'Japanese',
                    'native_name' => '日本語',
                    'script' => 'Japanese',
                    'direction' => 'ltr',
                    'iso_639_1' => 'ja',
                    'iso_639_2' => 'jpn',
                    'iso_639_3' => 'jpn',
                    'status' => 'active',
                ],
                [
                    'code' => 'ru',
                    'name' => 'Russe',
                    'name_en' => 'Russian',
                    'native_name' => 'Русский',
                    'script' => 'Cyrillic',
                    'direction' => 'ltr',
                    'iso_639_1' => 'ru',
                    'iso_639_2' => 'rus',
                    'iso_639_3' => 'rus',
                    'status' => 'active',
                ],
            ];

            foreach ($languages as $languageData) {
                $language = RecordLanguage::firstOrCreate(
                    ['code' => $languageData['code']],
                    $languageData
                );
                echo "✅ Langue créée: {$language->full_display} ({$language->code})\n";
            }

            echo "\n";

            // Migrate existing book language codes to language_id
            $books = RecordBook::withTrashed()->get();
            $updatedCount = 0;

            foreach ($books as $book) {
                // For test books, assign English language
                $language = RecordLanguage::where('code', 'en')->first();

                if ($language) {
                    $book->language_id = $language->id;
                    $book->save();
                    $updatedCount++;
                    echo "📚 Livre mis à jour: {$book->title} → {$language->name}\n";
                }
            }

            // Update book counts for all languages
            foreach (RecordLanguage::all() as $language) {
                $language->updateBookCount();
            }

            DB::commit();

            echo "\n✅ " . count($languages) . " langues créées\n";
            echo "✅ {$updatedCount} livres mis à jour\n";
            echo "✅ Migration terminée!\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "❌ Erreur: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
