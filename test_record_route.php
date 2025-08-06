<?php

use App\Models\Record;

// Test script pour diagnostiquer la redirection
$record = Record::latest()->first();

if ($record) {
    echo "Latest record ID: " . $record->id . PHP_EOL;
    echo "Route URL: " . route('records.show', $record->id) . PHP_EOL;
    echo "Record exists: " . (Record::where('id', $record->id)->exists() ? 'Yes' : 'No') . PHP_EOL;
} else {
    echo "No records found" . PHP_EOL;
}
