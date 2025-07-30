<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Test the authorType relationship
    $authors = \App\Models\Author::with('authorType')->get();
    echo "Success: Loaded " . $authors->count() . " authors with authorType relationship\n";

    if ($authors->count() > 0) {
        $firstAuthor = $authors->first();
        echo "First author: " . $firstAuthor->name . "\n";
        echo "Author type: " . ($firstAuthor->authorType ? $firstAuthor->authorType->name : 'No type') . "\n";
    }

    echo "Test completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
