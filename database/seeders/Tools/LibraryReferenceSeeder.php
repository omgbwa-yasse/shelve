<?php

namespace Database\Seeders\Tools;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LibraryReferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Languages
        $languages = [
            ['code' => 'fr', 'name' => 'FranÃ§ais', 'name_en' => 'French', 'iso_639_1' => 'fr'],
            ['code' => 'en', 'name' => 'Anglais', 'name_en' => 'English', 'iso_639_1' => 'en'],
            ['code' => 'es', 'name' => 'Espagnol', 'name_en' => 'Spanish', 'iso_639_1' => 'es'],
            ['code' => 'de', 'name' => 'Allemand', 'name_en' => 'German', 'iso_639_1' => 'de'],
            ['code' => 'it', 'name' => 'Italien', 'name_en' => 'Italian', 'iso_639_1' => 'it'],
            ['code' => 'pt', 'name' => 'Portugais', 'name_en' => 'Portuguese', 'iso_639_1' => 'pt'],
            ['code' => 'ru', 'name' => 'Russe', 'name_en' => 'Russian', 'iso_639_1' => 'ru'],
            ['code' => 'zh', 'name' => 'Chinois', 'name_en' => 'Chinese', 'iso_639_1' => 'zh'],
            ['code' => 'ja', 'name' => 'Japonais', 'name_en' => 'Japanese', 'iso_639_1' => 'ja'],
            ['code' => 'ar', 'name' => 'Arabe', 'name_en' => 'Arabic', 'iso_639_1' => 'ar', 'direction' => 'rtl'],
        ];

        foreach ($languages as $lang) {
            \App\Models\RecordLanguage::firstOrCreate(['code' => $lang['code']], $lang);
        }

        // Formats
        $formats = [
            ['name' => 'Poche', 'name_en' => 'Pocket', 'category' => 'pocket', 'dimensions_range' => '11x18 cm'],
            ['name' => 'Grand Format', 'name_en' => 'Large Format', 'category' => 'large', 'dimensions_range' => '15x24 cm'],
            ['name' => 'A4', 'name_en' => 'A4', 'category' => 'large', 'width_cm' => 21, 'height_cm' => 29.7],
            ['name' => 'A5', 'name_en' => 'A5', 'category' => 'standard', 'width_cm' => 14.8, 'height_cm' => 21],
            ['name' => 'Folio', 'name_en' => 'Folio', 'category' => 'folio', 'dimensions_range' => '> 30 cm'],
            ['name' => 'CarrÃ©', 'name_en' => 'Square', 'category' => 'special'],
        ];

        foreach ($formats as $format) {
            \App\Models\RecordBookFormat::firstOrCreate(['name' => $format['name']], $format);
        }

        // Bindings
        $bindings = [
            ['name' => 'BrochÃ©', 'name_en' => 'Paperback', 'category' => 'soft', 'durability_rating' => 5],
            ['name' => 'ReliÃ©', 'name_en' => 'Hardcover', 'category' => 'hard', 'durability_rating' => 9],
            ['name' => 'CartonnÃ©', 'name_en' => 'Board Book', 'category' => 'hard', 'durability_rating' => 8],
            ['name' => 'Spirale', 'name_en' => 'Spiral', 'category' => 'soft', 'durability_rating' => 4],
            ['name' => 'AgrafÃ©', 'name_en' => 'Stapled', 'category' => 'soft', 'durability_rating' => 3],
            ['name' => 'Cuir', 'name_en' => 'Leather', 'category' => 'hard', 'durability_rating' => 10],
        ];

        foreach ($bindings as $binding) {
            \App\Models\RecordBookBinding::firstOrCreate(['name' => $binding['name']], $binding);
        }

        // Publishers
        $publishers = [
            'Gallimard', 'Hachette', 'Flammarion', 'Seuil', 'Albin Michel',
            'Grasset', 'Fayard', 'Robert Laffont', 'Stock', 'Actes Sud',
            'Minuit', 'POL', 'Rivages', 'Le Livre de Poche', 'Pocket',
            '10/18', 'Points', 'Folio', 'J\'ai Lu', 'Babel',
            'Penguin', 'HarperCollins', 'Random House', 'Simon & Schuster', 'Macmillan',
            'Oxford University Press', 'Cambridge University Press', 'MIT Press'
        ];

        foreach ($publishers as $name) {
            \App\Models\RecordBookPublisher::firstOrCreate(['name' => $name]);
        }

        // Series (Collections)
        $series = [
            ['publisher' => 'Gallimard', 'name' => 'Blanche'],
            ['publisher' => 'Gallimard', 'name' => 'Folio'],
            ['publisher' => 'Gallimard', 'name' => 'La PlÃ©iade'],
            ['publisher' => 'Gallimard', 'name' => 'SÃ©rie Noire'],
            ['publisher' => 'Seuil', 'name' => 'Points'],
            ['publisher' => 'Actes Sud', 'name' => 'Babel'],
            ['publisher' => 'Minuit', 'name' => 'Double'],
            ['publisher' => 'Hachette', 'name' => 'BibliothÃ¨que Rose'],
            ['publisher' => 'Hachette', 'name' => 'BibliothÃ¨que Verte'],
        ];

        foreach ($series as $s) {
            $publisher = \App\Models\RecordBookPublisher::where('name', $s['publisher'])->first();
            if ($publisher) {
                \App\Models\RecordBookPublisherSeries::firstOrCreate(
                    ['name' => $s['name'], 'publisher_id' => $publisher->id]
                );
            }
        }
    }
}

