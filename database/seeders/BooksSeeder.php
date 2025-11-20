<?php

namespace Database\Seeders;

use App\Models\RecordBook;
use App\Models\RecordAuthor;
use App\Models\RecordBookCopy;
use App\Models\RecordBookPublisher;
use App\Models\RecordBookPublisherSeries;
use App\Models\RecordSubject;
use App\Models\BookClassification;
use App\Models\RecordBookFormat;
use App\Models\RecordBookBinding;
use App\Models\RecordLanguage;
use App\Models\User;
use Illuminate\Database\Seeder;

class BooksSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Get first user and organisation
        $user = User::first();
        $organisationId = 1;

        // Create or get publishers
        $prenticeHall = RecordBookPublisher::firstOrCreate(
            ['name' => 'Prentice Hall'],
            [
                'country' => 'US',
                'city' => 'Upper Saddle River, NJ',
                'founded_year' => 1913,
                'status' => 'active',
            ]
        );

        $harper = RecordBookPublisher::firstOrCreate(
            ['name' => 'Harper'],
            [
                'country' => 'US',
                'city' => 'New York',
                'founded_year' => 1817,
                'status' => 'active',
            ]
        );

        $oreilly = RecordBookPublisher::firstOrCreate(
            ['name' => 'O\'Reilly Media'],
            [
                'country' => 'US',
                'city' => 'Sebastopol, CA',
                'founded_year' => 1978,
                'status' => 'active',
            ]
        );

        // Create or get series
        $martinSeries = RecordBookPublisherSeries::firstOrCreate(
            [
                'publisher_id' => $prenticeHall->id,
                'name' => 'Robert C. Martin Series'
            ],
            [
                'description' => 'Professional software development series',
                'started_year' => 2002,
                'status' => 'active',
            ]
        );

        // Create or get formats
        $paperback = RecordBookFormat::firstOrCreate(['name' => 'Paperback']);
        $hardcover = RecordBookFormat::firstOrCreate(['name' => 'Hardcover']);
        $boxedSet = RecordBookFormat::firstOrCreate(['name' => 'Boxed Set']);

        // Create or get bindings
        $perfect = RecordBookBinding::firstOrCreate(['name' => 'Perfect']);
        $case = RecordBookBinding::firstOrCreate(['name' => 'Case']);

        // Create or get languages
        $english = RecordLanguage::firstOrCreate(['code' => 'en'], ['name' => 'English']);

        // Book 1: Programming book
        $book1 = RecordBook::firstOrCreate(
            ['isbn' => '978-0-13-468599-1'],
            [
            'title' => 'Clean Code',
            'subtitle' => 'A Handbook of Agile Software Craftsmanship',
            'publication_year' => 2008,
            'edition' => '1st',
            'place_of_publication' => 'Upper Saddle River, NJ',
            'pages' => 464,
            'format_id' => $paperback->id,
            'binding_id' => $perfect->id,
            'language_id' => $english->id,
            'dimensions' => '23.5 x 17.8 x 2.8 cm',
            'description' => 'Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees.',
            'table_of_contents' => 'Chapter 1: Clean Code; Chapter 2: Meaningful Names; Chapter 3: Functions...',
            'status' => 'active',
            'creator_id' => $user->id,
            'organisation_id' => $organisationId,
        ]);

        // Attach publisher and series
        if (!$book1->publishers()->where('publisher_id', $prenticeHall->id)->exists()) {
            $book1->publishers()->attach($prenticeHall->id);
        }
        if (!$book1->collections()->where('series_id', $martinSeries->id)->exists()) {
            $book1->collections()->attach($martinSeries->id);
        }

        // Add subjects
        $subjects1 = ['Software Development', 'Programming', 'Best Practices'];
        foreach ($subjects1 as $subjectName) {
            $subject = RecordSubject::firstOrCreate(['name' => $subjectName]);
            if (!$book1->subjects()->where('subject_id', $subject->id)->exists()) {
                $book1->subjects()->attach($subject->id);
            }
        }

        // Add classification (Dewey)
        $dewey1 = BookClassification::firstOrCreate(['name' => '005.1', 'description' => 'Computer programming, programs, data']);
        if (!$book1->classifications()->where('classification_id', $dewey1->id)->exists()) {
            $book1->classifications()->attach($dewey1->id);
        }

        // Add author
        $martinAuthor = RecordAuthor::firstOrCreate(
            ['full_name' => 'Robert C. Martin'],
            [
                'first_name' => 'Robert',
                'last_name' => 'Martin',
                'nationality' => 'US',
                'biography' => 'Software engineer and author known for Clean Code and SOLID principles.',
            ]
        );

        if (!$book1->authors()->where('author_id', $martinAuthor->id)->exists()) {
            $book1->authors()->attach($martinAuthor->id, [
                'role' => 'author',
                'display_order' => 1,
            ]);
        }

        // Add 3 copies
        for ($i = 1; $i <= 3; $i++) {
            RecordBookCopy::create([
                'book_id' => $book1->id,
                'barcode' => 'CC-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'call_number' => '005.1/MAR',
                'location' => 'Main Library',
                'shelf' => 'A-12',
                'status' => 'available',
                'condition' => 'good',
                'acquisition_date' => now()->subDays(rand(30, 365)),
                'acquisition_price' => 49.99,
                'acquisition_source' => 'Amazon',
            ]);
        }

        // Book 2: History book
        $book2 = RecordBook::firstOrCreate(
            ['isbn' => '978-0-14-303943-3'],
            [
            'title' => 'Sapiens',
            'subtitle' => 'A Brief History of Humankind',
            'publication_year' => 2015,
            'edition' => '1st',
            'place_of_publication' => 'New York',
            'pages' => 443,
            'format_id' => $hardcover->id,
            'binding_id' => $case->id,
            'language_id' => $english->id,
            'dimensions' => '24 x 16 x 3.5 cm',
            'description' => 'How did our species succeed in the battle for dominance? Why did our foraging ancestors come together to create cities and kingdoms?',
            'status' => 'active',
            'creator_id' => $user->id,
            'organisation_id' => $organisationId,
        ]);

        // Attach publisher
        if (!$book2->publishers()->where('publisher_id', $harper->id)->exists()) {
            $book2->publishers()->attach($harper->id);
        }

        // Add subjects
        $subjects2 = ['History', 'Anthropology', 'Human Evolution'];
        foreach ($subjects2 as $subjectName) {
            $subject = RecordSubject::firstOrCreate(['name' => $subjectName]);
            if (!$book2->subjects()->where('subject_id', $subject->id)->exists()) {
                $book2->subjects()->attach($subject->id);
            }
        }

        // Add classification (Dewey)
        $dewey2 = BookClassification::firstOrCreate(['name' => '909', 'description' => 'World history']);
        if (!$book2->classifications()->where('classification_id', $dewey2->id)->exists()) {
            $book2->classifications()->attach($dewey2->id);
        }

        // Add author
        $harariAuthor = RecordAuthor::firstOrCreate(
            ['full_name' => 'Yuval Noah Harari'],
            [
                'first_name' => 'Yuval Noah',
                'last_name' => 'Harari',
                'nationality' => 'IL',
                'birth_year' => 1976,
                'biography' => 'Israeli historian and author of Sapiens and Homo Deus.',
            ]
        );

        if (!$book2->authors()->where('author_id', $harariAuthor->id)->exists()) {
            $book2->authors()->attach($harariAuthor->id, [
                'role' => 'author',
                'display_order' => 1,
            ]);
        }

        // Add 5 copies
        for ($i = 1; $i <= 5; $i++) {
            RecordBookCopy::create([
                'book_id' => $book2->id,
                'barcode' => 'SAP-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'call_number' => '909/HAR',
                'location' => 'Main Library',
                'shelf' => 'B-05',
                'status' => 'available',
                'condition' => 'excellent',
                'acquisition_date' => now()->subDays(rand(30, 180)),
                'acquisition_price' => 35.00,
                'acquisition_source' => 'Barnes & Noble',
            ]);
        }

        // Book 3: Fiction book
        $book3 = RecordBook::firstOrCreate(
            ['isbn' => '978-0-06-112008-4'],
            [
            'title' => 'To Kill a Mockingbird',
            'publication_year' => 2006,
            'edition' => 'Reprint',
            'place_of_publication' => 'New York',
            'pages' => 324,
            'format_id' => $paperback->id,
            'binding_id' => $perfect->id,
            'language_id' => $english->id,
            'dimensions' => '20.3 x 13.5 x 2 cm',
            'description' => 'The unforgettable novel of a childhood in a sleepy Southern town and the crisis of conscience that rocked it.',
            'status' => 'active',
            'creator_id' => $user->id,
            'organisation_id' => $organisationId,
        ]);

        // Attach publisher
        if (!$book3->publishers()->where('publisher_id', $harper->id)->exists()) {
            $book3->publishers()->attach($harper->id);
        }

        // Add subjects
        $subjects3 = ['Fiction', 'Classic Literature', 'American Literature'];
        foreach ($subjects3 as $subjectName) {
            $subject = RecordSubject::firstOrCreate(['name' => $subjectName]);
            if (!$book3->subjects()->where('subject_id', $subject->id)->exists()) {
                $book3->subjects()->attach($subject->id);
            }
        }

        // Add classification (Dewey)
        $dewey3 = BookClassification::firstOrCreate(['name' => '813.54', 'description' => 'American fiction 1945-1999']);
        if (!$book3->classifications()->where('classification_id', $dewey3->id)->exists()) {
            $book3->classifications()->attach($dewey3->id);
        }

        // Add author
        $leeAuthor = RecordAuthor::firstOrCreate(
            ['full_name' => 'Harper Lee'],
            [
                'first_name' => 'Harper',
                'last_name' => 'Lee',
                'nationality' => 'US',
                'birth_year' => 1926,
                'death_year' => 2016,
                'status' => 'deceased',
                'biography' => 'American novelist best known for To Kill a Mockingbird.',
            ]
        );

        if (!$book3->authors()->where('author_id', $leeAuthor->id)->exists()) {
            $book3->authors()->attach($leeAuthor->id, [
                'role' => 'author',
                'display_order' => 1,
            ]);
        }

        // Add 2 copies
        for ($i = 1; $i <= 2; $i++) {
            RecordBookCopy::create([
                'book_id' => $book3->id,
                'barcode' => 'TKM-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'call_number' => '813.54/LEE',
                'location' => 'Main Library',
                'shelf' => 'F-22',
                'status' => 'available',
                'condition' => 'good',
                'acquisition_date' => now()->subDays(rand(180, 730)),
                'acquisition_price' => 15.99,
                'acquisition_source' => 'Local Bookstore',
            ]);
        }

        // Book 4: Science book with multiple authors
        $book4 = RecordBook::firstOrCreate(
            ['isbn' => '978-0-393-35457-6'],
            [
            'title' => 'The Feynman Lectures on Physics',
            'publication_year' => 2011,
            'edition' => 'New Millennium',
            'place_of_publication' => 'New York',
            'pages' => 1552,
            'format_id' => $boxedSet->id,
            'binding_id' => $hardcover->id,
            'language_id' => $english->id,
            'dimensions' => '26 x 18.5 x 12 cm',
            'description' => 'The whole thing was basically an experiment and Richard Feynman\'s experiment turned out to be hugely successful.',
            'status' => 'active',
            'creator_id' => $user->id,
            'organisation_id' => $organisationId,
        ]);

        // Attach publisher
        if (!$book4->publishers()->where('publisher_id', $oreilly->id)->exists()) {
            $book4->publishers()->attach($oreilly->id);
        }

        // Add subjects
        $subjects4 = ['Physics', 'Science', 'Education'];
        foreach ($subjects4 as $subjectName) {
            $subject = RecordSubject::firstOrCreate(['name' => $subjectName]);
            if (!$book4->subjects()->where('subject_id', $subject->id)->exists()) {
                $book4->subjects()->attach($subject->id);
            }
        }

        // Add classification (Dewey)
        $dewey4 = BookClassification::firstOrCreate(['name' => '530', 'description' => 'Physics']);
        if (!$book4->classifications()->where('classification_id', $dewey4->id)->exists()) {
            $book4->classifications()->attach($dewey4->id);
        }

        // Add authors
        $feynmanAuthor = RecordAuthor::firstOrCreate(
            ['full_name' => 'Richard P. Feynman'],
            [
                'first_name' => 'Richard',
                'last_name' => 'Feynman',
                'nationality' => 'US',
                'birth_year' => 1918,
                'death_year' => 1988,
                'status' => 'deceased',
                'biography' => 'American theoretical physicist and Nobel Prize winner.',
            ]
        );

        if (!$book4->authors()->where('author_id', $feynmanAuthor->id)->exists()) {
            $book4->authors()->attach($feynmanAuthor->id, [
                'role' => 'author',
                'display_order' => 1,
            ]);
        }

        $leightonAuthor = RecordAuthor::firstOrCreate(
            ['full_name' => 'Robert B. Leighton'],
            [
                'first_name' => 'Robert',
                'last_name' => 'Leighton',
                'nationality' => 'US',
            ]
        );

        if (!$book4->authors()->where('author_id', $leightonAuthor->id)->exists()) {
            $book4->authors()->attach($leightonAuthor->id, [
                'role' => 'author',
                'display_order' => 2,
            ]);
        }

        $sandsAuthor = RecordAuthor::firstOrCreate(
            ['full_name' => 'Matthew Sands'],
            [
                'first_name' => 'Matthew',
                'last_name' => 'Sands',
                'nationality' => 'US',
            ]
        );

        if (!$book4->authors()->where('author_id', $sandsAuthor->id)->exists()) {
            $book4->authors()->attach($sandsAuthor->id, [
                'role' => 'author',
                'display_order' => 3,
            ]);
        }

        RecordBookCopy::create([
            'book_id' => $book4->id,
            'barcode' => 'FLP-000001',
            'call_number' => '530/FEY',
            'location' => 'Reference Section',
            'shelf' => 'R-01',
            'status' => 'available',
            'condition' => 'excellent',
            'acquisition_date' => now()->subDays(60),
            'acquisition_price' => 150.00,
            'acquisition_source' => 'University Press',
        ]);

        $this->command->info('✅ Created 4 books with ' . RecordBookCopy::count() . ' copies');
        $this->command->info('✅ Added ' . RecordAuthor::count() . ' authors with ' .
            \Illuminate\Support\Facades\DB::table('record_author_book')->count() . ' book-author relations');
    }
}
