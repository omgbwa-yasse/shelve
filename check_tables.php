<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Vérification des tables keywords:\n";
echo "keywords: " . (Schema::hasTable('keywords') ? "existe" : "n'existe pas") . "\n";
echo "record_keyword: " . (Schema::hasTable('record_keyword') ? "existe" : "n'existe pas") . "\n";
echo "slip_record_keyword: " . (Schema::hasTable('slip_record_keyword') ? "existe" : "n'existe pas") . "\n";
