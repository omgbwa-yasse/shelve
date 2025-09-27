<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Database Check ===\n";
echo "Authors count: " . App\Models\Author::count() . "\n";
echo "Records count: " . App\Models\Record::count() . "\n";

echo "\n=== Search for 'Arnaude Boyer' ===\n";
$authors = App\Models\Author::where('name', 'LIKE', '%Arnaude%')
    ->orWhere('first_name', 'LIKE', '%Arnaude%')
    ->orWhere('last_name', 'LIKE', '%Boyer%')
    ->get();

if ($authors->count() > 0) {
    foreach ($authors as $author) {
        echo "Found author: " . $author->name . " " . $author->first_name . " " . $author->last_name . "\n";

        // Check if this author has any records
        $records = $author->records()->count();
        echo "  - Has {$records} associated records\n";
    }
} else {
    echo "No authors found matching 'Arnaude Boyer'\n";
}

echo "\n=== Search in Records for 'Arnaude Boyer' ===\n";
$records = App\Models\Record::whereHas('authors', function($query) {
    $query->where('name', 'LIKE', '%Arnaude%')
          ->orWhere('first_name', 'LIKE', '%Arnaude%')
          ->orWhere('last_name', 'LIKE', '%Boyer%');
})->get();

if ($records->count() > 0) {
    foreach ($records as $record) {
        echo "Found record: " . $record->name . " (ID: {$record->id})\n";
    }
} else {
    echo "No records found for 'Arnaude Boyer'\n";
}

echo "\n=== All Authors (first 10) ===\n";
$allAuthors = App\Models\Author::limit(10)->get();
foreach ($allAuthors as $author) {
    echo "- " . ($author->name ?: '') . " " . ($author->first_name ?: '') . " " . ($author->last_name ?: '') . "\n";
}

unlink(__FILE__);