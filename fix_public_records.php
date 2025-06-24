<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Record;
use App\Models\PublicRecord;

// Script pour corriger les record_id dans public_records

echo "Correction des record_id dans public_records...\n";

$recordIds = Record::pluck('id')->toArray();
$publicRecords = PublicRecord::all();

foreach ($publicRecords as $index => $publicRecord) {
    if (isset($recordIds[$index])) {
        $oldRecordId = $publicRecord->record_id;
        $publicRecord->record_id = $recordIds[$index];
        $publicRecord->save();

        echo "Updated PublicRecord {$publicRecord->id}: record_id {$oldRecordId} -> {$recordIds[$index]}\n";
    }
}

echo "Vérification: " . PublicRecord::whereHas('record')->count() . " PublicRecords avec relations valides\n";
echo "Terminé!\n";
