<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$service = $app->make(App\Services\GitHubApiService::class);

echo "Testing GitHub API connection...\n";

$latest = $service->getLatestRelease();

if ($latest) {
    echo "✅ Success! Latest release: " . $latest['tag_name'] . "\n";
    echo "Released: " . $latest['published_at'] . "\n";
} else {
    echo "❌ Failed to fetch latest release\n";
}

$releases = $service->getAllReleases();
echo "Found " . count($releases) . " releases\n";

foreach (array_slice($releases, 0, 3) as $release) {
    echo "- " . $release['tag_name'] . " (" . $release['published_at'] . ")\n";
}
