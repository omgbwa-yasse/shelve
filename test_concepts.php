<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$concepts = App\Models\ThesaurusConcept::with(['labels', 'scheme'])->limit(3)->get();

foreach ($concepts as $concept) {
    $preferredLabel = $concept->labels->where('type', 'prefLabel')->first();
    echo $concept->id . ': ' . ($preferredLabel ? $preferredLabel->literal_form : 'No label') . ' - ' . ($concept->scheme ? $concept->scheme->title : 'No scheme') . PHP_EOL;
}
